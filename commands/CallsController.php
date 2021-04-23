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
use app\models\Call;
use app\models\Device;
use app\models\Settings;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;

class CallsController extends Controller
{
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

    public function actionIndex()
    {
        if ($this->device) {
            Console::output("Dial from devices: " . implode(',', $this->device));
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output("Dial from ALL registered devices");
            $devices = Device::find()->all();
        }
        foreach ($devices as $device) {
            Console::output("Device ID: $device->id");
            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
            foreach ($device->getLines($device->user_id)->all() as $line) {
                if (!$line->simcard_id) {
                    Console::output("Line $line->id skipped due missing SIM card");
                    continue;
                }
                $dial_test_number = Settings::findOne([
                    'name' => 'dial_test_number',
                    'user_id' => $device->getAttribute('user_id'),
                ]);
                if (empty($dial_test_number)) continue;
                $dial_test_number = $dial_test_number->getAttribute('value');
                $dial_test_interval = Settings::findOne([
                    'name' => 'dial_test',
                    'user_id' => $device->getAttribute('user_id'),
                ])->getAttribute('value');
                // TODO: Settings getter by name & user_id
                $simcard = $line->simcard;
                Console::output("Dialing test number " . $dial_test_number . " using simcard " . $simcard->id);
                $last_call = $simcard->getLastCall($dial_test_number);
                $last_call_tm = isset($last_call) ? strtotime($last_call->getAttribute('tm')) : 0;
                if (time() - $last_call_tm > $dial_test_interval) {
                    try {
                        $goip->dial->call($line->number, $dial_test_number, 15, false);
                    } catch (GoipUnavailableException $e) {
                        continue;
                    }
                    $call = new Call();
                    $call->tm = new Expression('NOW()');
                    $call->simcard_id = $line->simcard_id;
                    $call->phone = $dial_test_number;
                    $call->save();
                    $device->tm_activity = new Expression('NOW()');
                    $device->save();
                    Console::output("Test number $dial_test_number dialed using simcard $simcard->id");
                } else {
                    Console::output("Simcard $simcard->id skipped due user setting");
                }
            }
        }
    }
}
