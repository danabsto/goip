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

use app\models\Settings;
use Exception;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SettingsController implements the CRUD actions for Settings model.
 */
class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
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
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all Settings models.
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Settings::find()->where(['user_id' => Yii::$app->user->id])
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Creates a new Settings model.
     * If creation is successful, the browser will be redirected to the 'update' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Settings();
        $model->user_id = Yii::$app->user->getId();
        if ($this->chooseSettings($model) && $model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect('index');
        $settingsAvailable = Settings::getAvailableDefaultSettings($model);
        return $this->render('create', [
            'model' => $model,
            'settings' => $settingsAvailable,
        ]);
    }

    /**
     * @param Settings $model
     * @return boolean
     */
    protected function chooseSettings(Settings $model): bool
    {
        if (Yii::$app->request->isAjax) {
            $current = $model->getAttributes();
            $model->load(Yii::$app->request->post());
            $model->value = null;
            if ($current['name'] == $model->name) $model->value = $current['value'];
            return false;
        }
        return true;
    }

    /**
     * Updates an existing Settings model.
     * If update is successful, the browser will be redirected to the 'update' page.
     * @param string $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(string $id)
    {
        $model = $this->findModel($id);
        if ($this->chooseSettings($model) && $model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['update', 'id' => $model->name]);
        return $this->render('update', [
            'model' => $model,
            'settings' => Settings::getAvailableDefaultSettings($model),
        ]);
    }

    /**
     * Finds the Settings model based on its name and using current user's ID.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(string $id): Settings
    {
        if (($model = Settings::findOne(['name' => $id, 'user_id' => Yii::$app->user->id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Deletes an existing Settings model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return Response
     * @throws UserException
     */
    public function actionDelete(string $id): Response
    {
        $setting = Settings::findOne(['user_id' => Yii::$app->user->id, 'name' => $id]);
        try {
            $setting->delete();
        } catch (Exception $e) {
            throw new UserException("Ой! Кажется что-то пошло не так: {$e->getCode()} {$e->getMessage()}");
        }
        return $this->redirect(['index']);
    }
}
