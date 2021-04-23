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
use app\models\Device;
use app\models\Line;
use app\models\Operator;
use app\models\Simcard;
use Exception;
use yii\console\Controller;
use yii\helpers\Console;

class StatusController extends Controller
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
            Console::output('Get status for devices: ' . implode(',', $this->device));
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output('Get status for ALL registered devices');
            $devices = Device::find()->all();
        }
        foreach ($devices as $device) {
            Console::output("device #$device->id - $device->title");
            try {
                $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
                $lines = $goip->line->getInfo();
                foreach ($lines as $line) {
                    $deviceLine = Line::find()->where(['device_id' => $device->id, 'number' => $line['id']])->one();
                    Console::output("line #$deviceLine->number - $deviceLine->title");
                    $deviceLine->imsi = $line['imsi'];
                    $deviceLine->imei = $line['imei'];
                    if (!$deviceLine) die("error with imsi {$line['number']}");
                    if ($line['iccid'] == '' && $deviceLine->simcard_id) {
                        $deviceLine->simcard_id = null;
                        $deviceLine->save();
                    } else if ($line["iccid"] != "") {
                        $simcard = Simcard::find()->where(['iccid' => $line['iccid']])->one();
                        $operator = Operator::find()->where(['name' => $line['operator']])->one();
                        if (!$simcard) {
                            if (!$operator) {
                                $operator = new Operator();
                                $operator->name = $line['operator'];
                                $operator->save();
                            }
                            $simcard = new Simcard();
                            $simcard->iccid = $line['iccid'];
                            $simcard->phone = $line['phone'];
                            $simcard->operator_id = $operator->id;
                            $simcard->save();
                        }
                        $deviceLine->simcard_id = $simcard->id;
                        if (!$deviceLine->save()) print_r($deviceLine->getErrors());
                    }
                }
            } catch (Exception $e) {
                Console::output("{$e->getCode()}: {$e->getMessage()}");
                continue;
            }
        }
    }
}