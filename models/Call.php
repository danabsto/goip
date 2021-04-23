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
 * This is the model class for table "calls".
 *
 * @property integer $id
 * @property integer $simcard_id
 * @property string $phone
 * @property string $tm
 */
class Call extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'calls';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['simcard_id'], 'integer'],
            [['tm'], 'safe'],
            [['phone'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'simcard_id' => 'Simcard ID',
            'phone' => 'Phone',
            'tm' => 'Tm',
        ];
    }
}
