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

use app\models\traits\DropDownListTrait;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%goip_operators}}".
 *
 * @property integer $id
 * @property string
 * @property Line[] $lines
 * @property string $get_balance_ussd [varchar(255)]
 * @property string $get_phone_ussd [varchar(255)]
 * @property string $get_balance_regexp [varchar(255)]
 * @property string $get_phone_regexp [varchar(255)]
 * @property string $name [varchar(255)]
 */
class Operator extends ActiveRecord
{
    use DropDownListTrait;

    const USSD_PATTERN_MASK = '[\*9{1,20}]{1,10}\#';
    const USSD_PATTERN = '/(?:\*[0-9]{1,20}){1,10}#/';

    const LIST_USSD = ['balance', 'phone_number'];

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'operators';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name', 'get_balance_ussd', 'get_balance_regexp', 'get_phone_ussd', 'get_phone_regexp'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('goip', 'ID'),
            'title' => Yii::t('goip', 'Title'),
            'balance' => Yii::t('goip', 'Balance'),
            'balance_pattern' => Yii::t('goip', 'Balance pattern'),
            'phone_number' => Yii::t('goip', 'Phone Number'),
            'phone_number_pattern' => Yii::t('goip', 'Phone Number pattern'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getLines(): ActiveQuery
    {
        return $this->hasMany(Line::className(), ['operator_id' => 'id']);
    }
}
