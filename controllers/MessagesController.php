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
use app\models\Line;
use app\models\Message;
use app\models\ShareMessages;
use app\models\Simcard;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * MessagesController implements the CRUD actions for Messages model.
 */
class MessagesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'send', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'send', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    private function getQuery($id = null, $limit = null)
    {
        $cols = ['messages.*',
            'devices.title AS device_title',
            'device_lines.title AS line_title',
            'simcards.phone AS simcard_phone',
            'devices.user_id AS user_id'];
        $query = Message::find()
            ->select($cols)
            ->leftJoin('share_message_simcard', 'messages.simcard_id = share_message_simcard.simcard_id')
            ->leftJoin('share_messages', 'share_message_simcard.share_message_id = share_messages.id')
            ->leftJoin('device_lines', 'messages.simcard_id = device_lines.simcard_id')
            ->leftJoin('devices', 'device_lines.device_id = devices.id')
            ->leftJoin('simcards', 'messages.simcard_id = simcards.id');
        $shares = ShareMessages::find()->where(['share_to' => Yii::$app->user->id]);
        if (!empty($id)) {
            $query = $query->andWhere(['messages.simcard_id' => $id]);
            $shares = $shares->innerJoin('share_message_simcard', 'share_messages.id = share_message_simcard.share_message_id');
            $shares = $shares->andWhere(['share_message_simcard.simcard_id' => $id]);
        }
        $result = clone $query;
        if (!empty($id)) {
            $result = $result->andWhere(['messages.simcard_id' => $id]);
        }
        $result = $result->andWhere(['devices.user_id' => Yii::$app->user->id]);
        if (!empty($limit)) {
            $result = $result->orderBy(['tm' => SORT_DESC]);
            $result = $result->limit($limit);
        }
        $shares = $shares->all();
        foreach ($shares as $share) {
            $shared_messages = clone $query;
            $shared_messages = $this->filterQuery($shared_messages, $share);
            if (!empty($id)) {
                $shared_messages = $shared_messages->andWhere(['messages.simcard_id' => $id]);
            }
            $shared_simcard_ids = Simcard::find()
                ->select('simcards.id')
                ->innerJoinWith('shares')
                ->where([
                    'share_to' => Yii::$app->user->id,
                    'share_messages.id' => $share->id,
                ]);
            $shared_messages = $shared_messages->andWhere(['in', 'simcards.id', $shared_simcard_ids]);
            if (!empty($limit)) {
                $shared_messages = $shared_messages->orderBy(['tm' => SORT_DESC]);
                $shared_messages = $shared_messages->limit($limit);
            }
            $result->union($shared_messages);
        }

        return (new \yii\db\Query())
            ->from(['dummy_name' => $result])
            ->orderBy(['tm' => SORT_DESC]);
    }

    public function actionArchive($id = null)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->getQuery($id),
            'pagination' => false,
        ]);

        $line = !empty($id) ? Simcard::findOne(["id" => $id])->line : null;

        return $this->render('archive', [
            'dataProvider' => $dataProvider,
            'line' => $line,
            'devices' => Device::getDevices(Yii::$app->user->id, true),
            'messages_per_page' => 20,
        ]);
    }

    /**
     * Lists all Messages models.
     * @param int $id
     * @return mixed
     */
    public function actionIndex($id = null)
    {
        $result = $this->getQuery($id, 20);

        $dataProvider = new ActiveDataProvider([
            'query' => $result,
            'pagination' => false,
        ]);

        $line = !empty($id) ? Simcard::findOne(["id" => $id])->line : null;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'line' => $line,
            'devices' => Device::getDevices(Yii::$app->user->id, true),
            'messages_per_page' => 20,
        ]);
    }

    /**
     * Creates a new Messages model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'lines' => Line::getDropDownList(),
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Messages model.
     * If update is successful, the browser will be refresh page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
        }

        return $this->render('update', [
            'lines' => Line::getDropDownList(),
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing Messages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Messages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('goip', 'The requested page does not exist.'));
        }
    }

    public function actionSend()
    {
        if(\Yii::$app->request->isPost) {
            $lineId = \Yii::$app->request->post("line");
            $line = Line::find()->where(["id" => $lineId])->one();
            $line->simcard_id;

            $messages = \Yii::$app->request->post("messages");
            $messages = explode("\n", $messages);
            $messages = array_filter($messages);
            foreach($messages as $message) {
                list($phone, $text) = explode(";", $message);

                $m = new Message();
                $m->type = 0;
                $m->status = 0;
                $m->simcard_id = $line->simcard_id;
                $m->text = $text;
                $m->address = $phone;
                $m->tm_create = new Expression("NOW()");
                $m->save();
            }

            \Yii::$app->session->setFlash("success", Yii::t('goip', 'Messages queued for sending!'));
            return $this->redirect(["messages/send"]);
        }


        $items = [];
        $devices = Device::find()->all();
        foreach ($devices as $device) {
            foreach ($device->getLines($device->user_id)->all() as $line) {
                $items[$device->title][$line->id] = $line->title;
            }
        }

        return $this->render("send", [
            "items" => $items
        ]);
    }

    private function filterQuery($query, $share)
    {
        $filters = json_decode($share['filters'], true);
        $conditions = $filters['conditions'];
        if (!isset($conditions)) {
            return $query;
        }
        $comparison_condition = $filters['comparison_condition'];
        $currentCondition = 0;
        foreach ($conditions as $condition) {
            $regexp = 'REGEXP';
            if (substr_count(strtolower($condition['condition']), 'matches')) {
                $condition['text'] = '^' . $condition['text'] . '$';
            }
            if (substr_count(strtolower($condition['condition']), 'doesnt')) {
                $regexp = 'NOT ' . $regexp;
            }
            switch ($comparison_condition) {
                case 'and':
                        $query->andWhere([$regexp, 'messages.' . $condition['field'], $condition['text']]);
                    break;
                case 'or':
                    if ($currentCondition > 0) {
                        $query->orWhere([$regexp, 'messages.' . $condition['field'], $condition['text']]);
                    } else {
                        $query->andWhere([$regexp, 'messages.' . $condition['field'], $condition['text']]);
                    }
                    break;
            }
            $currentCondition += 1;
        }
        return $query;
    }
}
