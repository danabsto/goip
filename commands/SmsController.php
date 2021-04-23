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

namespace app\commands;

use app\components\goip\GoipApi;
use app\components\GoipUnavailableException;
use app\models\Device;
use app\models\Line;
use app\models\Message;
use Exception;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class SmsController extends Controller
{
    const TEST_PHONE_NUMBER = '+79161234567';
    const TEST_MESSAGE = 'This is a test message';

    var $device = [];   // параметр идентификатора устройств, например --device=1,2,5

    /**
     * Формирование списка общих параметров для команд
     * @param $actionID - название команды
     * @return array|string[]
     */
    public function options($actionID): array
    {
        $options = ['index' => []];
        $global_options = ['device'];
        return ($actionID && array_key_exists($actionID, $options)) ?
            array_merge($options[$actionID], $global_options) : $global_options;
    }

    /**
     * @throws Exception
     */
    public function actionBlock()
    {
        if ($this->device) {
            Console::output("Block devices: " . implode(',', $this->device));
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output("Block ALL registered devices");
            $devices = Device::find()->all();
        }
        foreach ($devices as $device) {
            Console::output("Device ID: " . $device->id);
            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
            foreach ($device->getLines($device->user_id)->all() as $line) {
                if (ArrayHelper::getValue($line, ["simcard", "operator", "id"]) == 1) {
                    try {
                        $goip->sms->send($line->number, 5151, "УСТЗАПРЕТСП");
                    } catch (GoipUnavailableException $e) {
                        continue;
                    }
                    $device->tm_activity = new Expression('NOW()');
                    $device->save();
                }
            }
        }
    }

    public function actionBillSms()
    {
        if ($this->device) {
            Console::output("Get bill sms for devices: " . implode(',', $this->device));
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output("Get bill sms for ALL registered devices");
            $devices = Device::find()->all();
        }
        foreach ($devices as $device) {
            foreach ($device->getLines($device->user_id)->all() as $line) {
                $m = new Message();
                $m->simcard_id = $line->simcard_id;
                $m->text = self::TEST_MESSAGE;
                $m->tm_create = new Expression('NOW()');
                $m->status = 0;
                $m->address = self::TEST_PHONE_NUMBER;
                $m->type = 0;
                $m->save();
            }
        }
    }

    public function actionSend()
    {
        $messages = Message::find()->where(["type" => 0, "status" => 0])->all();
        foreach ($messages as $message) {
            $simcard = $message->simcard;
            $line = $simcard->line;
            $device = $line->device;
            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
            try {
                $goip->sms->send($line["number"], $message->address, $message->text);
            } catch (GoipUnavailableException $e) {
                continue;
            }
            $message->status = 1;
            $message->tm_send = new Expression('NOW()');
            $message->save();
            sleep(5);
            $device->tm_activity = new Expression('NOW()');
            $device->save();
        }
    }

    public function actionGet()
    {
        if ($this->device) {
            Console::output('Get sms messages for devices: ' . implode(',', $this->device));
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output('Get sms messages for ALL registered devices');
            $devices = Device::find()->all();
        }
        foreach ($devices as $device) {
            Console::output("Device ID: $device->id");
            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
            try {
                $allMessages = $goip->sms->getMessages();
            } catch (GoipUnavailableException $e) {
                Console::output("{$e->getCode()}: {$e->getMessage()}");
                continue;
            }
            $device->tm_activity = new Expression('NOW()');
            $device->save();
            foreach ($allMessages as $lineNum => $messages) {
                $line = Line::find()->where(['device_id' => $device->id, 'number' => $lineNum])->one();
                foreach ($messages as $message) {
                    $m = Message::find()->where(['tm_create' => date('Y') . '-' . $message['date'],
                        'address' => $message['sender'],
                        'simcard_id' => $line->simcard_id])->one();
                    if (!$m) {
                        $m = new Message();
                        $m->simcard_id = $line->simcard_id;
                        $m->tm_create = date("Y") . '-' . $message['date'];
                        $m->address = $message["sender"];
                        $m->text = $message["text"];
                        $m->tm = date("Y-m-d H:i:s");
                        $m->save();
                    }
                }
                try {
                    $goip->sms->clear($lineNum);
                } catch (GoipUnavailableException $e) {
                    Console::output("{$e->getCode()}: {$e->getMessage()}");
                    continue;
                }
            }
        }
    }
}
