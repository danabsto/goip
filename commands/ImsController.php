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

use app\models\Device;
use app\models\IMS;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class ImsController extends Controller
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
     * Вывод общего изменения баланса по каждому устройству
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionBalance()
    {
        $devices = Device::find()->all();
        foreach ($devices as $device) {
            Console::output("Device '$device->title' (Id: $device->id):");
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
            Console::output("\tDay: " . Yii::$app->formatter->asCurrency($balance_day, "RUB"));
            Console::output("\tWeek: " . Yii::$app->formatter->asCurrency($balance_week, "RUB"));
            Console::output("\tMonth: " . Yii::$app->formatter->asCurrency($balance_month, "RUB"));
        }
    }

    /**
     * @return bool
     */
    public function actionFlush(): bool
    {
        $imses = IMS::findAll(['account_id' => null]);
        if (empty($imses)) return false;
        foreach ($imses as $ims) {
            Console::output("Flushing ims with code $ims->vcode");
            if (strtotime($ims->tm_created) < time() - 60 * 60 * 24) {
                try {
                    $ims->delete();
                    Console::output("Successfully flushed ims with code $ims->vcode");
                } catch (Exception $e) {
                    Console::output("Can not flush ims with code $ims->vcode: 
                        {$e->getCode()} {$e->getMessage()}");
                }
            }
        }
        Console::output('Tokens flushed successfully');
        return true;
    }
}
