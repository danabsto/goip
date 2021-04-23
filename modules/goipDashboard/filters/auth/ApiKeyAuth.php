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

namespace app\modules\goipDashboard\filters\auth;

use yii\filters\auth\HttpHeaderAuth;

/**
 * ApiKeyAuth.php
 *
 * @author Alexandra Kovshova <akovshova@gmail.com>
 * @copyright 2021 by Alexandra Kovshova
 */
class ApiKeyAuth extends HttpHeaderAuth
{
    /**
     * {@inheritdoc}
     */
    public $header = 'Authorization';

    /**
     * {@inheritdoc}
     */
    public $pattern = '/^ApiKey=(.*?)$/';

    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"$this->realm\"");
    }
}
