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
 * Class Dial
 * @package app\components\goip
 */
class Dial extends Basic
{
    const ROUTE = "/tools.html";
    const STATUS = "/dial.xml";

    /**
     * @param int $line
     * @param int $phone
     * @param int $duration
     * @param bool $wait
     * @return bool
     *
     * @throws GoipUnavailableException
     */
    public function call(int $line, int $phone, int $duration = 10, $wait = true): bool
    {
        $key = $this->getKey("dial", "dial");
        $this->postRequest(self::ROUTE, ["type" => "dial"], [
            "line" => $line,
            "dialkey" => $key,
            "action" => "dial",
            "telnum" => '+' . $phone,
            "duration" => $duration,
            "dial" => "Dial"
        ]);
        sleep(1);
        return !$wait || $this->isComplete($line);
    }

    /**
     * @param int $line
     * @return bool
     * @throws GoipUnavailableException
     */
    private function isComplete(int $line): bool
    {
        $isDone = false;
        while (!$isDone) {
            sleep(1);
            $result = $this->getRequest(self::STATUS);
            preg_match_all("/<line(\d+)_gsm_status>([a-zA-Z0-9]+?)<\/line(\d+)_gsm_status>(\s+)<line(\d+)_state>(.*?)<\/line(\d+)_state>/mius", $result, $m);
            if ($m[6][$line - 1] == "IDLE") $isDone = true;
        }
        return true;
    }

    /**
     * @param int $line
     * @return mixed
     *
     * @throws GoipUnavailableException
     */
    public function check(int $line)
    {
        $result = $this->getRequest(self::STATUS);
        preg_match_all("/<line(\d+)_gsm_status>([a-zA-Z0-9]+?)<\/line(\d+)_gsm_status>(\s+)<line(\d+)_state>(.*?)<\/line(\d+)_state>/mius", $result, $m);
        return $m[6][$line - 1];
    }
}