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

namespace app\components\goip;

use app\components\GoipUnavailableException;

/**
 * Class Ussd
 * @package GoipApi
 */
class Ussd extends Basic
{

    const ROUTE_SEND = '/ussd_info.html';
    const ROUTE_STATUS = '/send_sms_status.xml';

    /**
     * @param $code
     * @param null $lineID
     * @return mixed
     *
     * @throws GoipUnavailableException
     */
    public function send($code, $lineID = null)
    {
        $key = $this->getKey("sms", "ussd");
        $this->postRequest(self::ROUTE_SEND, ["type" => "USSD"], [
            'action' => 'USSD',
            'send' => 'Send',
            'smskey' => $key,
            'telnum' => $code,
            'line' . $lineID => 1
        ]);
        $answers = $this->getAnswers($key);
        return $answers[$lineID] ?? false;
    }

    /**
     * @param $key
     * @return array
     *
     * @throws GoipUnavailableException
     */
    private function getAnswers($key): array
    {
        $isDone = false;
        $answers = [];
        $try = 0;
        while (!$isDone) {
            $try++;
            sleep(1);
            $result = $this->getRequest(self::ROUTE_STATUS, ['line' => '', 'ajaxcachebust' => microtime()]);
            preg_match_all("/<smskey(\d+)>([a-z0-9]+?)<\/smskey(\d+)>(\s+)<status(\d+)>(.*?)<\/status(\d+)>(\s+)<error(\d+)>(.*?)<\/error(\d+)>/mius", $result, $m);
            $all = $done = 0;
            foreach ($m[2] as $i => $mkey) {
                if ($key != $mkey) continue;
                $lineID = $m[1][$i];
                $status = $m[6][$i];
                $text = $m[10][$i];
                $answers[$lineID] = ["status" => $status, "text" => $text];
                $all++;
                if ($status == "DONE") $done++;
            }
            if ($all == $done) $isDone = true;
            if ($try == 20) $isDone = true;
        }
        return $answers;
    }
}