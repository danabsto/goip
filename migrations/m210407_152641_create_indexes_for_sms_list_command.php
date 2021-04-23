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

use yii\db\Migration;

/**
 * Class m210407_152641_create_indexes_for_sms_list_command
 */
class m210407_152641_create_indexes_for_sms_list_command extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('db_user_api_key_index', '{{%user}}', 'api_key', true);
        $this->createIndex('devices_user_id_index', 'devices', 'user_id');
        $this->createIndex('device_lines_simcard_id_device_id_index', 'device_lines', ['simcard_id', 'device_id']);
        $this->createIndex('device_lines_device_id_index', 'device_lines', 'device_id');
        $this->createIndex('messages_simcard_id_index', 'messages', 'simcard_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('messages_simcard_id_index', 'messages');
        $this->dropIndex('device_lines_device_id_index', 'device_lines');
        $this->dropIndex('device_lines_simcard_id_device_id_index', 'device_lines');
        $this->dropIndex('devices_user_id_index', 'devices');
        $this->dropIndex('db_user_api_key_index', '{{%user}}');
    }
}
