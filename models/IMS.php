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
 * This is the model class for table "ims".
 *
 * @property integer $id
 * @property string $service
 * @property string $user_id
 * @property string $name
 * @property string $settings
 * @property boolean $active
 * @property string $vcode
 * @property string $tm_created
 * @property int $account_id [int
 */
class IMS extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'ims';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['user_id', 'name'], 'string', 'max' => 255],
            [['tm_created'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'service' => 'Type',
            'user_id' => 'User Id',
            'name' => 'Username',
            'active' => 'Active',
            'tm_created' => 'Created'
        ];
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return json_decode($this->settings, true);
    }

    /**
     * @param $settings
     */
    public function setSettings($settings)
    {
        $this->settings = json_encode($settings);
    }

}
