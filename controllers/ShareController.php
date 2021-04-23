<?php
/**
 * @copyright Copyright 2021 Undefined.team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\controllers;

use app\models\Device;
use app\models\ShareCondition;
use app\models\ShareMessages;
use app\models\Simcard;
use dektrium\user\models\User;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class ShareController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index', 'device'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'device'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /** Поиск пользователей для подсказки в форме */
    public function actionSearch($term)
    {
        $results = [];
        foreach (User::find()->where(['like', 'username', $term . '%', false])
                     ->orWhere(['like', 'email', $term . '%', false])
                     ->andWhere(['<>', 'id', Yii::$app->user->id])->all() as $model) {
            $results[] = [
                'id' => $model['id'],
                'label' => $model['username'] . " ({$model['email']})",
            ];
        }
        echo json_encode($results);
    }

    /**
     * Выводит список шар сообщений для устройства
     * @param $id
     * @return string
     * @throws UserException
     */
    public function actionDevice($id): string
    {
        $device = $this->findDevice($id);
        $dataProvider = new ActiveDataProvider([
            'query' => ShareMessages::find()
                ->innerJoinWith('lines')
                ->where(['device_lines.device_id' => $id])
                ->orderBy('device_lines.id'), // TODO: add groupBy('share_messages.id')
            'pagination' => false,
        ]);
        return $this->render('device', [
            'dataProvider' => $dataProvider,
            'device' => $device,
        ]);
    }

    private function findDevice(?int $device_id): Device
    {
        $device = Device::findOne(['id' => $device_id, 'user_id' => Yii::$app->user->id]);
        if (empty($device))
            throw new UserException('Share not found or you don\'t have permission to modify this share');
        return $device;
    }

    /**
     * создает новую шару для устройства
     * @param integer $id - идентификтор записи устройства
     * @throws UserException
     */
    public function actionCreate(int $id)
    {
        $device = $this->findDevice($id);
        $simcards = Simcard::getSharedSimcards($id);
        $share = new ShareMessages();
        $shareMessages = Yii::$app->request->post('ShareMessages');
        if (isset($shareMessages)) {
            $share->user_id = Yii::$app->user->id;
            $share->device_id = $id;
            $share->tm_updated = date('Y-m-d H:i:s');
        }
        if (!empty($shareMessages->simcard_ids))
            $share->simcard_ids = $$shareMessages->simcard_ids;
        if ($share->load(Yii::$app->request->post()) && $share->save()) {
            return $this->redirect(['device', 'id' => $device->id]);
        } else {
            return $this->render('device_update', [
                'simcards' => $simcards,
                'device' => $device,
                'share' => $share,
                'shareConditions' => [new ShareCondition]
            ]);
        }
    }

    /**
     * создает новую шару для устройства
     * @param integer $id идентификатор записи SharedMessage для редактирования
     * @throws UserException
     */
    public function actionUpdate(int $id)
    {
        $shares = ShareMessages::find()->all();
        foreach ($shares as $share) {
            $simcards = $share->simcards;
            if (empty($simcards)) continue;
            $line = $simcards[0]->line;
            if (empty($line)) continue;
            $device = $line->device;
            if (empty($device)) continue;
            $share->setAttribute('device_id', $device->getAttribute('id'));
        }
        $share = $this->findModel($id);
        $device = $this->findDevice($share->device_id);
        $simcards = Simcard::getSharedSimcards($device->id);
        foreach ($share->simcards as $simcard) $share->simcard_ids[] = $simcard->id;
        $shareConditionsRaw = json_decode($share->filters);
        $share->comparison_condition = $shareConditionsRaw->comparison_condition;
        if (isset($shareConditionsRaw->conditions)) {
            $shareConditions = [];
            foreach ($shareConditionsRaw->conditions as $shareConditionRaw) {
                $shareCondition = new ShareCondition();
                $shareCondition->field = $shareConditionRaw->field;
                $shareCondition->condition = $shareConditionRaw->condition;
                $shareCondition->text = $shareConditionRaw->text;
                $shareCondition->isNewRecord = false;
                array_push($shareConditions, $shareCondition);
            }
        }
        if ($share->load(Yii::$app->request->post()) && $share->save()) {
            return $this->redirect(['device', 'id' => $device->id]);
        } else {
            $user_id = $share->share_to;
            $user = User::findOne($user_id);
            $share->share_to_email = $user->email;
            return $this->render('device_update', [
                    'simcards' => $simcards,
                    'device' => $device,
                    'share' => $share,
                    'shareConditions' => $shareConditions ?? [new ShareCondition]]
            );
        }
    }

    /**
     * @param $id
     * @return ShareMessages
     * @throws UserException
     */
    protected function findModel($id): ShareMessages
    {
        $model = ShareMessages::FindOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if (empty($model))
            throw new UserException('Share not found or you don\'t have permission to modify this share');
        return $model;
    }

    /**
     * Удаляет шару для устройства
     * @param integer $id идентификатор записи SharedMessage для редактирования
     */
    public function actionDelete(int $id): Response
    {
        $share = $this->findModel($id);
        $device_id = $share->device_id;
        $share->delete();
        return $this->redirect(['device', 'id' => $device_id]);
    }
}
