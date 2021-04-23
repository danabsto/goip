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

use app\components\goip\GoipApi;
use app\components\GoipUnavailableException;
use app\models\Line;
use app\models\Operator;
use app\models\Simcard;
use Yii;
use app\models\Device;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * DevicesController implements the CRUD actions for Devices model.
 */
class DevicesController extends Controller
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
                'only' => ['index', 'view', 'create', 'update', 'delete', 'lines', 'reboot'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'lines'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['reboot'],
                        'allow' => true,
                        'matchCallback' => function($rule, $action) {
                            return Device::findOne(['id' => Yii::$app->request->get('id'),
                                'user_id' => Yii::$app->user->id]);
                        }
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all Devices models.
     * @return mixed
     */
    public function actionIndex()
    {

        $dataProvider = new ActiveDataProvider([
            "query" => Device::find()->where(["user_id" => \Yii::$app->user->getId()])
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSettings($id)
    {
        $model = Device::findOne(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('goip', 'Device settings updated'));
        }

        return $this->render('settings', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Devices model.
     * If creation is successful, the browser will be redirected to the 'update' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Device();
        $model->user_id = \Yii::$app->user->getId();

        $model->setAttribute('display_empty_lines',
            ArrayHelper::getValue(Yii::$app->request->post('Device'), 'display_empty_lines', true));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // регистрируем линии добавленного устройства
            $goipApi = new GoipApi($model->host, $model->port, $model->login, $model->password);
            $model->registerLines($goipApi);

            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionView($id) {
        $model = Device::find()->where(["id" => $id, "user_id" => \Yii::$app->user->getId()])->one();
        $goip = new GoipApi($model->host, $model->port, $model->login, $model->password);

        $lines = $goip->line->getInfo();

        if(!count($model->getLines($model->user_id)->all())) {
            foreach($lines as $line) {
                $operator = null;
                if(trim((string)$line["operator"]) != "") {
                    $operator = Operator::find()->where(["name" => $line["operator"]])->one();
                    if(!$operator) {
                        $operator = new Operator();
                        $operator->name = $line["operator"];
                        $operator->save();
                    }
                }
                $simcard = null;
                if(trim((string)$line["iccid"]) != "") {
                    $simcard = Simcard::find()->where(["iccid" => $line["iccid"]])->one();
                    if(!$simcard) {
                        $simcard = new Simcard();
                        $simcard->iccid = (string)$line["iccid"];
                        $simcard->phone = (string)$line["phone"];
                        $simcard->operator_id = ArrayHelper::getValue($operator, "id", null);
                        $simcard->save();
                    }
                }
                $deviceLine = new Line();
                $deviceLine->device_id  = $model->id;
                $deviceLine->number     = $line["id"];
                $deviceLine->title      = "Линия #".$line["id"];
                $deviceLine->imei       = (string)$line["imei"];
                $deviceLine->imsi       = (string)$line["imsi"];
                $deviceLine->simcard_id = ArrayHelper::getValue($simcard, "id", null);
                if(!$deviceLine->save()) {
                    print_r($deviceLine->getErrors()); die();
                }
            }
            $model->refresh();
        }

        return $this->render('view', ["model" => $model]);
    }

    /**
     * Updates an existing Devices model.
     * If update is successful, the browser will be refresh page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('goip', 'Device settings updated'));
        }

        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing Devices model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionLines()
    {
        $parents = Yii::$app->request->post('depdrop_all_params', null);
        if (isset($parents) && isset($parents['device_id'])) {
            $id = $parents['device_id'];
            Yii::$app->response->format = Response::FORMAT_JSON;
            $excludeNumbers = [];

            if (
                !empty($parents['device_id_current']) &&
                !empty($parents['number_current']) &&
                $parents['device_id_current'] == $id
            ) {
                $excludeNumbers[] = $parents['number_current'];
            }

            $result = Device::getDropDownLines($id, true, $excludeNumbers);


            if (count($result)) {
                return ['output' => $result, 'selected' => $result[0]];
            }
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }


    /**
     * Finds the Devices model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Device the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Device::findOne(["id" => $id, "user_id" => \Yii::$app->user->id])) !== null) {
            return $model;
        } else {
            throw new UserException('Device not found or you don\'t have access rights to this device');
        }
    }

    /**
     * @param int $id Device id
     * @return string View
     * @throws UserException
     */
    public function actionReboot(int $id): string
    {
        $device = Device::findOne(['id' => $id]);
        $request = Yii::$app->request;
        if ($request->isPost) {
            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
            try {
                $result = $goip->device->reboot();
                Yii::debug($result);
            } catch (GoipUnavailableException $e) {
                throw new UserException($e->getMessage());
            }
            Yii::$app->session->setFlash('success', Yii::t('goip', 'Device rebooted'));
        }
        return $this->render('reboot', ['model' => $device]);
    }
}
