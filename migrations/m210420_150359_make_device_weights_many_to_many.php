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

use app\models\Device;
use app\models\DeviceWeight;
use yii\db\Migration;

/**
 * Class m210420_150359_make_device_weights_many_to_many
 */
class m210420_150359_make_device_weights_many_to_many extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!$this->getDb()->getTableSchema('device_weights')) {
            $this->createTable('device_weights', [
                'id' => $this->primaryKey(),
                'device_id' => $this->integer()->notNull(),
                'user_id' => $this->integer()->notNull(),
                'weight' => $this->integer()->notNull()->defaultValue(DeviceWeight::DEFAULT_WEIGHT),
            ]);
            $this->createIndex('device_weights_device_id_user_id_index', 'device_weights', [
                'device_id', 'user_id'
            ]);
        }
        $devices = Device::find()->all();
        foreach ($devices as $device) {
            if (!empty($device->user_id)) {
                $this->insert('device_weights', [
                    'device_id' => $device->id,
                    'user_id' => $device->user_id,
                    'weight' => $device->weight ?? 0,
                ]);
            }
        }
        if ($this->getDb()->getTableSchema('devices')
            and $this->getDb()->getTableSchema('devices')->getColumn('weight')) {
            $this->dropColumn('devices', 'weight');
        }
    }

    /**
     * {@inheritdoc}
     */
    public
    function safeDown()
    {
        // Caution! All device weights will be set to 0 after rollback!
        if ($this->getDb()->getTableSchema('devices')
            and !$this->getDb()->getTableSchema('devices')->getColumn('weight')) {
            $this->addColumn('devices', 'weight', $this->integer()->notNull()->defaultValue(0));
        }
        if ($this->getDb()->getTableSchema('device_weights')) {
            $this->dropTable('device_weights');
        }
    }
}
