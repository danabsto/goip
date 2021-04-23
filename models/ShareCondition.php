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

use Yii;
use yii\base\Model;

/**
 * Share condition model.
 * Needed for dynamic condition field adding inside form
 */
class ShareCondition extends Model
{
    public $field = '';
    public $condition = '';
    public $text = '';
    public $isNewRecord = true;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['field', 'condition', 'text'], 'required'],
            [['field', 'condition', 'text'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('goip', 'from'),
            'field' => Yii::t('goip', 'condition_field'),
            'condition' => Yii::t('goip', 'condition_condition'),
            'text' => Yii::t('goip', 'condition_text'),
            'matches' => Yii::t('goip', 'matches'),
            'doesntMatch' => Yii::t('goip', 'doesn\'t match'),
            'contains' => Yii::t('goip', 'contains'),
            'doesntContain' => Yii::t('goip', 'doesn\'t contain'),
        ];
    }
}
