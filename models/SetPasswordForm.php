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

class SetPasswordForm extends Model
{
    public $oldpassword;
    public $password;
    public $repassword;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['oldpassword', 'password', 'repassword'], 'required'],
            ['password', 'string', 'min' => 5],
            ['repassword', 'compare', 'compareAttribute' => 'password'],
            ['oldpassword', 'checkOldPassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'oldpassword' => Yii::t('app', 'Current password'),
            'password' => Yii::t('app', 'New password'),
            'repassword' => Yii::t('app', 'Repeat new password'),
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkOldPassword($attribute, $params)
    {
        $user = Yii::$app->user->identity;
        if (!$user->validatePassword($this->oldpassword)) {
            $this->addError($attribute, Yii::t('app', 'Invalid current password specified'));
        }
    }
}