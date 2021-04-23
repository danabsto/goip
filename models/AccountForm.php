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

class AccountForm extends Model
{
    public $apikey;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['apikey'], 'required'],
            ['apikey', 'string', 'min' => 8]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'apikey' => Yii::t('app', 'Apikey')
        ];
    }
}