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
 * Class Basic
 * @package GoipApi
 */
abstract class Basic
{
    const ROUTE_DEFAULT = "/default/en_US";
    const ROUTE_KEY = '/tools.html';
    public $_goip;

    /**
     * Basic constructor.
     * @param GoipApi $goip
     */
    function __construct(GoipApi $goip)
    {
        $this->_goip = $goip;
    }

    /**
     * @param string $route
     * @param array $params
     * @param array $data
     * @return bool|string
     * @throws GoipUnavailableException
     */
    public function postRequest(string $route, array $params = [], array $data = [])
    {
        $curl = $this->prepareRequest($route, $params);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $return = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($errno > 0) throw new GoipUnavailableException($error);
        return $return;
    }

    /**
     * @param string $route
     * @param array $params
     * @return resource
     */
    private function prepareRequest(string $route, array $params = [])
    {
        $curl = curl_init("http://{$this->_goip->host}:{$this->_goip->port}"
            . self::ROUTE_DEFAULT . $route . '?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->_goip->login}:{$this->_goip->password}");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 15000);
        return $curl;
    }

    /**
     * @param string $name
     * @param string $type
     * @return string|null
     * @throws GoipUnavailableException
     */
    public function getKey(string $name, string $type): ?string
    {
        $keyPage = $this->getRequest(self::ROUTE_KEY, ["type" => $type]);
        return preg_match('/name="' . $name . 'key" value="([a-z0-9]+)"/', $keyPage, $key) ? $key[1] : null;
    }

    /**
     * @param string $route
     * @param array $params
     * @return bool|string
     * @throws GoipUnavailableException
     */
    public function getRequest(string $route, array $params = [])
    {
        $curl = $this->prepareRequest($route, $params);
        $return = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($errno > 0) throw new GoipUnavailableException($error);
        return $return;
    }
}