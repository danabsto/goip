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

namespace app\modules\goipDashboard\controllers\api;

use app\components\goip\GoipApi;
use app\components\GoipUnavailableException;
use app\models\Device;
use app\models\Line;
use app\models\Message;
use app\models\Simcard;
use app\modules\goipDashboard\filters\auth\ApiKeyAuth;
use Yii;
use yii\filters\AccessControl;
use yii\rest\Controller;


class SmsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => ApiKeyAuth::className(),
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['list-by-phone', 'send'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['list-by-phone', 'send'],
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return Yii::$app->user->identity->isAdmin;
                    }
                ],
            ],
        ];
        return $behaviors;
    }

    public function init()
    {
        parent::init();
        Yii::$app->user->enableSession = false;
    }

    /**
     * Получение списка SMS сообщений для указанного устройства и линии
     *
     * @param int $device_id
     * @param int $line
     * @param int $limit
     * @param string $sender
     * @return Message[]
     */
    public function actionList(int $device_id, int $line, $limit = 5, $sender = null): array
    {
        $user_id = Yii::$app->user->identity->getId();
        $device = Device::find()->where(["id" => $device_id])->andWhere(['user_id' => $user_id])->one();
        if (!$device) return [
            'success' => false,
            'error' => [
                'type' => 'DeviceNotFound',
                'message' => 'specified device not found'
            ]
        ];
        $line = Line::find()->where(["device_id" => $device->id, "number" => $line])->one();
        if (!$line) return [
            'success' => false,
            'error' => [
                'type' => 'LineNotFound',
                'message' => 'specified line not found'
            ]
        ];
        $messages = $sender ? $line->getMessagesBySender($sender) : $line->messages;
        return array_slice($messages, 0, $limit);
    }

    /**
     * Получение SMS сообщений для линии с установленным телефонным номером sim-карты
     *
     * @param string $phone
     * @param int $limit
     * @return Message[]
     */
    public function actionListByPhone(string $phone, $limit = 5): array
    {
        // TODO добавить проверку что устройство принадлежит пользователю

        $simcard = Simcard::find()->where(["phone" => $phone])->one();
        if (!$simcard) return [
            'success' => false,
            'error' => [
                'type' => 'PhoneNotFound',
                'message' => 'specified phone number not found'
            ]
        ];
        $line = $simcard->line;
        $messages = $line->messages;
        return array_slice($messages, 0, $limit);
    }

    /**
     * Отправка текcтового SMS сообщения
     *
     * @param int $device_id
     * @param int $line
     * @param string $companion
     * @param string $text
     * @return array|bool[]
     * @throws GoipUnavailableException
     */
    public function actionSend(int $device_id, int $line, string $companion, string $text): array
    {
        $user_id = Yii::$app->user->identity->getId();
        $device = Device::find()->where(["id" => $device_id])->andWhere(['user_id' => $user_id])->one();
        if (!$device) return [
            'success' => false,
            'error' => [
                'type' => 'DeviceNotFound',
                'message' => 'specified device not found'
            ]
        ];
        $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
        $goip->sms->send($line, $companion, $text);
        return ['success' => true];
    }
}