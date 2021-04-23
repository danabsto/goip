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
 * Class Config
 * @package GoipApi
 */
class Config extends Basic
{
    const ROUTE = "/config.html";

    /**
     * @throws GoipUnavailableException
     */
    public function setForward($line, $phone, $s = 1): bool
    {
        $this->postRequest(self::ROUTE, ["type" => "sim_forward"], [
            "line_sim_conf_tab" => "line{$line}_sim_conf",
            "line{$line}_gsm_cf_uncnd_enable" => $s,
            "line{$line}_gsm_cf_uncnd_num" => $phone,
            "line{$line}_gsm_cf_busy_enable" => $s,
            "line{$line}_gsm_cf_busy_num" => $phone,
            "line{$line}_gsm_cf_noreply_enable" => $s,
            "line{$line}_gsm_cf_noreply_num" => $phone,
            "line{$line}_gsm_cf_notreachable_enable" => $s,
            "line{$line}_gsm_cf_notreachable_num" => $phone
        ]);
        return true;
    }
}