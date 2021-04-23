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
use app\models\Balance;
use app\models\Device;
use app\models\IMS;
use app\models\Settings;
use TelegramBot\Api\Client;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class BalancesController extends Controller
{

    var $device = [];   // параметр идентификатора устройств, например --device=1,2,5

    /**
     * Формирование списка общих параметров для команд
     * @param $actionID - название команды
     * @return array|string[]
     */
    public function options($actionID)
    {
        $options = [
            'index' => [],
        ];
        $global_options = [
            'device'
        ];

        return ($actionID && array_key_exists($actionID,  $options)) ?
            array_merge($options[$actionID], $global_options) : $global_options;
    }

    public function actionIndex()
    {
        if ($this->device) {
            Console::output("Get balance for devices: " . implode(',', $this->device));
            /* @var $devices \app\models\Device[] */
            $devices = Device::find()->where(["id" => $this->device])->all();
        } else {
            Console::output("Get balance for ALL registered devices");
            /* @var $devices \app\models\Device[] */
            $devices = Device::find()->all();
        }

        $bot_messages = [];

        foreach ($devices as $device) {
            Console::output("Device ID: " . $device->id);

            $goip = new GoipApi($device->host, $device->port, $device->login, $device->password);

            foreach ($device->getLines($device->user_id)->all() as $line) {
                Console::output("Processing line " . $line->id);
                $simcard = $line->simcard;
                if (!isset($simcard) or empty($simcard->phone)) {
                    Console::output("No simcard in line " . $line->id);
                    continue;
                }

                Console::output("Processing simcard " . $simcard->phone);
                $balance = $simcard->getBalance()->one();
                $tm = isset($balance) ? strtotime($balance->getAttribute('tm')) : 0;

                Console::output("Checking balance settings for simcard " . $simcard->phone);
                $settings = Settings::findOne(['name' => 'check_balance', 'user_id' => $device->user_id]);
                $check_balance = isset($settings) ? $settings->getAttribute('value') : 0;

                if (time() - $tm > $check_balance) {
                    Console::output("Updating balance for simcard " . $simcard->phone);

                    Console::output("Running USSD for simcard " . $simcard->phone);
                    $ussd = ArrayHelper::getValue($simcard, ["operator", "get_balance_ussd"], false);
                    if ($ussd) {
                        Console::output("Ran USSD for simcard " . $simcard->phone);
                        try {
                            $result = $goip->ussd->send($simcard->operator->get_balance_ussd, $line->number);
                        } catch (GoipUnavailableException $e) {
                            continue;
                        }
                        Console::output("Applying simcard regexp for simcard " . $simcard->phone);
                        if ($result and preg_match($simcard->operator->get_balance_regexp, $result["text"], $m)) {
                            Console::output("Saving new balance for simcard " . $simcard->phone);
                            $m[0] = preg_replace("/,/", ".", $m[0]);
                            $m[0] = preg_replace("/Минус:/", "-", $m[0]);
                            $m[0] = preg_replace("/Баланс:/", "", $m[0]);
                            $old_balance = $simcard->balance;
                            $simcard->balance = floatval($m[0]);
                            $simcard->save();

                            Console::output("Saving balance log for simcard " . $simcard->phone);
                            $b = new Balance();
                            $b->simcard_id = $simcard->id;
                            $b->balance = $simcard->balance;
                            $b->tm = new Expression('NOW()');
                            $b->save();

                            $balance_diff = $old_balance - $simcard->balance;
                            if ($balance_diff > 0) {
                                $shares = $simcard->shares;
                                $user_ids = [$device->user_id];
                                foreach ($shares as $share) {
                                    if (!in_array($share->share_to, $user_ids)) {
                                        $user_ids[] = $share->share_to;
                                    }
                                }
                                foreach ($user_ids as $user_id) {
                                    $bot_messages[$user_id]['last_activity'] = time() - strtotime($device->tm_activity);
                                    $bot_messages[$user_id]['devices'][$device->title][$line->number] = [
                                        'title' => !empty($line->title)
                                            ? $line->title
                                            : Yii::t('goip', 'Линия {number}', ['number' => $line->number]),
                                        'balance_diff' => $balance_diff
                                    ];
                                }
                            }
                        }
                        Console::output("Saved all balances for simcard " . $simcard->phone);

                        Console::output("Updating activity for simcard " . $simcard->phone);
                        $device->tm_activity = new Expression('NOW()');
                        $device->save();
                        Console::output("Updated activity for simcard " . $simcard->phone);
                    }

                    Console::output("Done updating balance for simcard " . $simcard->phone);
                }
                Console::output("Done checking balance settings for simcard " . $simcard->phone);
            }
        }

        $bot = new Client(Yii::$app->params['ims']['telegram']['token']);
        foreach ($bot_messages as $user_id => $bot_message) {
            $chats = IMS::find()->where(['account_id' => $user_id, 'active' => true])->all();
            foreach ($chats as $chat) {
                $user_settings = json_decode($chat->settings, true);
                $bot->sendMessage($user_settings['chat_id'],
                    $this->renderPartial('_message.php', ['message' => $bot_message]));
                Console::output("Bot sent message for user ");
            }
        }
    }
}
