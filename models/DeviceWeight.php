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

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "device_weights".
 *
 * @property int $id
 * @property int $device_id
 * @property int $user_id
 * @property int $weight
 */
class DeviceWeight extends ActiveRecord
{
    const DEFAULT_WEIGHT = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'device_weights';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['device_id', 'user_id'], 'required'],
            [['device_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'device_id' => 'Device ID',
            'user_id' => 'User ID',
        ];
    }
}
