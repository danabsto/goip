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

use Exception;

/**
 * Class GoipApi
 * @package GoipApi
 *
 * @property Line $line
 * @property Ussd $ussd
 * @property Sms $sms
 * @property Dial $dial
 * @property Config $config
 * @property Device $device
 *
 * @property string $host
 * @property string $port
 * @property string $login
 * @property string $password
 */
class GoipApi
{
    protected $_host;
    protected $_port;
    protected $_login;
    protected $_password;

    protected $config = [];

    /**
     * GoipApi constructor.
     * @param $host
     * @param $port
     * @param $login
     * @param $password
     */
    public function __construct($host, $port, $login, $password)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_login = $login;
        $this->_password = $password;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        $propertyName = "_" . mb_strtolower($name);
        if (property_exists($this, $propertyName)) return $this->$propertyName;
        $name = ucfirst($name);
        $className = "\\app\\components\\goip\\" . $name;
        if (!class_exists($className)) throw new Exception("Class $name not exists");
        return new $className($this);
    }
}