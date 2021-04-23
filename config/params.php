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

return [
    'adminEmail' => '<email>',

    'ims' => [
        'telegram' => [
            'bot_name' => '<bot_name>',
            'token' => '<token>',
		// to register webhook for telegram bot use command
		// curl -v https://api.telegram.org/bot<token>/setwebhook?url=https://<site_url>/ims/webhook?key=<service_key>
        ],
        'service_keys' => [
            '<service_key>' => 'handleTelegramWebhook',
        ]
    ],
    'api' => [
        'key' => '<app_api_key>',
    ]
];
