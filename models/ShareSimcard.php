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

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "share_message_simcard".
 *
 * @property int $share_message_id
 * @property int $simcard_id
 * @property int $id [int]
 */
class ShareSimcard extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'share_message_simcard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['share_message_id', 'simcard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'share_message_id' => 'Share message ID',
            'simcard_id' => 'Simcard ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getShare(): ActiveQuery
    {
        return $this->hasOne(ShareMessages::class, ['id' => 'share_message_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSimcard(): ActiveQuery
    {
        return $this->hasMany(Simcard::class, ['id' => 'simcard_id']);
    }
}
