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
use app\models\ContactForm;
use app\models\Device;
use app\models\IMS;
use app\models\Line;
use app\models\Settings;
use app\models\SignupForm;
use app\models\User;
use Yii;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'check-balance', 'send-sms', 'send-call', 'add-balance', 'set-forward', 'reload'],
                'rules' => [
                    [
                        'actions' => ['logout', 'check-balance', 'send-sms', 'send-call', 'add-balance', 'set-forward', 'reload'],
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
    public function actions()
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if (Yii::$app->user->isGuest) {
			$this->layout = 'lp';
			return $this->render('lp');
		}
        $devices = Device::getDevices(Yii::$app->user->id, true);
        return $this->render('index', ['devices' => $devices]);
    }

    public function actionReload($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $line = $this->getLine($id);
        $device = $line->device;

        $goip = new GoipApi(
            $device->host,
            $device->port,
            $device->login,
            $device->password
        );

        $goip->line->changeStatus($line["number"]);
        sleep(2);
        $goip->line->changeStatus($line["number"]);
        sleep(60);
        return ["success" => 1];
    }

    public function actionCheckBalance($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $line = $this->getLine($id);
        $device = $line->device;
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        $simcard = $line->simcard;
        $result = $goip->ussd->send($line->simcard->operator->get_balance_ussd, $line->number);
        if (preg_match($line->simcard->operator->get_balance_regexp, $result["text"], $m)) {
            $m[0] = preg_replace("/Минус:/", "-", $m[0]);
            $m[0] = preg_replace("/Баланс:/", "", $m[0]);
            $simcard->balance = $m[0];
            $simcard->save();
            $bd = $simcard->balance - ArrayHelper::getValue($line, ["simcard.yesterdayBalance.balance"]);
            if ($bd < 0) {
                $bot = new \TelegramBot\Api\Client(\Yii::$app->params['ims']['telegram']['token']);
                $chats = IMS::find()->where(['account_id' => \Yii::$app->getUser()->getId(), 'active' => true])->all();
                foreach ($chats as $c) {
                    $user_settings = json_decode($c->settings, true);
                    $bot->sendMessage($user_settings['chat_id'], "Проверить симку слоте №{$line->number} в гоипе {$device->title} (Id: {$device->id}) т.к. за сутки списали {$bd}р.");
                }
            }
        }
        return [
            'balance' => Yii::$app->formatter->asCurrency($simcard->balance, 'RUB'),
            'color' => $simcard->balance > 0 ? 'green' : 'red'
        ];
    }

    /**
     * @return array
     * @throws UserException
     */
    public function actionSendSms(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $phone = Yii::$app->request->post('phone');
        $message = Yii::$app->request->post('message');
        $line = $this->getLine($id);
        $device = $line->device;
        $phone = preg_replace('/\D/', '', $phone);
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        try {
            $goip->sms->send($line['number'], $phone, $message);
        } catch (\Exception $e) {
            return ['success' => 0, 'result' => $e->getMessage()];
        }
        return ['success' => 1, 'result' => 'СМС отправлено'];
    }

    /**
     * @return array|int[]
     * @throws UserException
     */
    public function actionSetForward(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $phone = Yii::$app->request->post('phone');
        $s = Yii::$app->request->post('s', 1);
        $line = $this->getLine($id);
        $device = $line->device;
        $phone = preg_replace('/\D/', '', $phone);
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        try {
            $goip->config->setForward($line["number"], $phone, $s);
            $line->forward = $s ? (string)$phone : null;
            $line->save();
        } catch (\Exception $e) {
            return ['success' => 0, 'result' => $e->getMessage()];
        }
        return ['success' => 1];
    }

    /**
     * @return array|int[]
     * @throws UserException
     */
    public function actionSendCall(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $phone = Yii::$app->request->post('phone');
        $duration = Yii::$app->request->post('duration');
        $line = $this->getLine($id);
        $device = $line->device;
        $phone = preg_replace('/\D/', '', $phone);
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        try {
            $goip->dial->call($line['number'], $phone, $duration, false);
        } catch (\Exception $e) {
            return ['success' => 0, 'result' => $e->getMessage()];
        }
        return ['success' => 1];
    }

    /**
     * @return array
     * @throws \app\components\GoipUnavailableException|UserException
     */
    public function actionCheckCall(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->get('id');
        $line = $this->getLine($id);
        $device = $line->device;
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        $result = $goip->dial->check($line['number']);
        return ['result' => $result];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionSendUssd(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $ussd = Yii::$app->request->post('ussd');
        $line = $this->getLine($id);
        $device = $line->device;
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        try {
            $result = $goip->ussd->send($ussd, $line['number']);
        } catch (\Exception $e) {
            return ['success' => 0, 'result' => $e->getMessage()];
        }
        return ['success' => 1, 'result' => ArrayHelper::getValue($result, 'text')];
    }

    /**
     * @param $id
     * @return Line
     * @throws UserException
     */
    protected function getLine($id): Line
    {
        $model = Line::getLinesQuery(Yii::$app->user->id, true)
            ->andWhere(['device_lines.id' => $id])->one();
        if (empty($model))
            throw new UserException('Line not found or you don\'t have access rights to this line');
        return $model;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            return $this->goBack();
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionContacts() {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('success', Yii::t('goip', 'Thank you for contacting us. Soon we will answer you.'));

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionRu()
    {
        $cookie = new \yii\web\Cookie([
            'name' => 'lang',
            'value' => 'ru-RU',
            'expire' => 0,
        ]);

        Yii::$app->response->cookies->add($cookie);

        return $this->redirect(['index']);
    }

    public function actionEn()
    {
        $cookie = new \yii\web\Cookie([
            'name' => 'lang',
            'value' => 'en-US',
            'expire' => 0,
        ]);

        Yii::$app->response->cookies->add($cookie);

        return $this->redirect(['index']);
    }
}
