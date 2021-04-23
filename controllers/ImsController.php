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
use app\models\IMS;
use app\models\IMSForm;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ImsController - Instant Message Services
 * @package app\controllers
 */
class ImsController extends Controller
{
    /* список ключей для вызовов webhook сервисов, простая шифровка */
    protected $service_keys = [];

    /**
     * ImsController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        $this->service_keys[] = Yii::$app->params['ims']['service_keys'];
        parent::__construct($id, $module, $config);
    }

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
                'only' => ['index', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all IMS models.
     * @return string|Response
     */
    public function actionIndex()
    {
        $imsConnections = new ActiveDataProvider([
            'query' => IMS::find()->where(['account_id' => Yii::$app->user->id])
        ]);
        // проверка кода и подключение чата к аккаунту
        $imsCode = new IMSForm();
        if ($imsCode->load(Yii::$app->request->post()) && $imsCode->validate()) {
            $imsEntry = IMS::find()->where(['vcode' => $imsCode->validation_code])->one();
            if ($imsEntry == null) {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'IMS validation code invalid or not found'));
                return $this->refresh();
            }
            $imsEntry->account_id = Yii::$app->getUser()->getId();
            $imsEntry->active = true;
            $imsEntry->vcode = null;
            if ($imsEntry->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'IMS connection added'));
                return $this->refresh();
            }
        }
        return $this->render('index', [
            'imsConnections' => $imsConnections,
            'imsCode' => $imsCode,
        ]);
    }

    public function actionDelete($id): Response
    {
        $model = IMS::findOne(['id' => $id, 'account_id' => Yii::$app->user->id]);
        if (empty($model))
            throw new UserException('IMS not found or you don\'t have access rights to this IMS');
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Webhook для отбработки запросов пока только к боту телеграмма
     */
    public function actionWebhook()
    {
        $service_key = Yii::$app->request->get("key");
        if (array_key_exists($service_key, $this->service_keys))
            return call_user_func([$this, $this->service_keys[$service_key]]);
        throw new NotFoundHttpException();
    }

    /**
     * Код обработки запросов для telegram бота
     */
    protected function handleTelegramWebhook()
    {
        $token = Yii::$app->params['ims']['telegram']['token'];
        try {
            $bot = new Client($token);
            // команда для start
            $bot->command('start', function ($message) use ($bot) {
                $answer = <<<EOL
                    Добро пожаловать в систему GOIP!!
                    Для подключения к аккаунту наберите команду /login
                EOL;
                $bot->sendMessage($message->getChat()->getId(), $answer);
            });
            $bot->command('login', function ($message) use ($bot) {
                try {
                    $tlg_user = new IMS();
                    $tlg_user->service = 'telegram';
                    $tlg_user->user_id = $message->getFrom()->getUsername();
                    $tlg_user->name = $message->getFrom()->getFirstName() . " " . $message->getFrom()->getLastName();
                    $tlg_user->active = false;
                    $tlg_user->tm_created = date('Y-m-d H:i:s');
                    $tlg_user->vcode = Yii::$app->security->generateRandomString(6);
                    $tlg_user->setSettings(['chat_id' => $message->getChat()->getId()]);
                    $tlg_user->save(false);
                    $answer = "Код для подключения - " . $tlg_user->vcode . "\n
                        Необходимо добавить его на странице Настройки/IMS connections\nКод действителен в течении 24 часов.";
                } catch (Exception $e) {
                    $answer = 'Ошибка добавления. Повторите запрос чуть позже';
                }
                $bot->sendMessage($message->getChat()->getId(), $answer);
            });
            /** информация об изменении баланса по устройствам */
            $bot->command('balance', function ($message) use ($bot) {
                // проверка прав доступа к функции
                $connect = IMS::find()->where(['service' => 'telegram',
                    'user_id' => $message->getFrom()->getUsername(), 'active' => true])->one();
                if (!$connect) {
                    $bot->sendMessage($message->getChat()->getId(), 'В доступе отказано');
                    return;
                }
                $answer = "Изменение баланса за день:\n";
                try {
                    $devices = Device::getDevices($connect->getAttribute('account_id'));
                    foreach ($devices as $device) {
                        $answer .= "  Device '$device->title' (Id: $device->id): ";
                        $balance_month = 0;
                        $balance_week = 0;
                        $balance_day = 0;
                        foreach ($device['lines'] as $line) {
                            $balance = ArrayHelper::getValue($line, "simcard.balance");
                            $bd = ArrayHelper::getValue($line, ["simcard.yesterdayBalance.balance"]);
                            $bw = ArrayHelper::getValue($line, ["simcard.weekBalance.balance"]);
                            $bm = ArrayHelper::getValue($line, ["simcard.monthBalance.balance"]);
                            $balance_day += $balance - $bd;
                            $balance_week += $balance - $bw;
                            $balance_month += $balance - $bm;
                        }
                        $answer .= Yii::$app->formatter->asCurrency($balance_day, "RUB") . "\n";
                    }
                } catch (Exception $e) {
                    $answer = 'Ошибка обработки баланса. Повторите запрос чуть позже';
                }
                $bot->sendMessage($message->getChat()->getId(), $answer);
            });
            // команда для помощи
            $bot->command('help', function ($message) use ($bot) {
                $answer = <<<EOL
                    'Команды:
                    /help - вывод справки
                    /login - получение кода подключения к системе
                    /balance - изменение баланса за день по всем устройствам
                    EOL;
                $bot->sendMessage($message->getChat()->getId(), $answer);
            });
            $bot->command('info', function ($message) use ($bot) {
                $answer = var_export($message->getChat(), true);
                $bot->sendMessage($message->getChat()->getId(), $answer);
            });
            $bot->run();
        } catch (Exception $e) {
        }
    }
}