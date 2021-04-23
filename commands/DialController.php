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
use app\models\Settings;
use yii\console\Controller;
use yii\helpers\Console;

class DialController extends Controller
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

    /**
     * @throws GoipUnavailableException
     */
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
            $dial_test_number = Settings::findOne(['user_id' => $device->user_id, 'name' => 'dial_test_number']);
            foreach ($device->getLines($device->user_id)->all() as $line) {
                if (empty($dial_test_number)) continue;
                $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);
                $goip->dial->call($line["number"], $dial_test_number->value);
            }
        }
    }
}