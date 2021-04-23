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
use yii\base\Exception;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class SignupForm extends Model
{
    public $username;
    public $password;
    public $repassword;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['username', 'password', 'repassword'], 'required'],
            ['password', 'compare', 'compareAttribute' => 'repassword'],
            ['username', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'login', 'message' => Yii::t('goip', 'This login is already taken')],
            ['username', 'filter', 'filter' => 'trim']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('goip', 'Username'),
            'password' => Yii::t('goip', 'Password'),
            'repassword' => Yii::t('goip', 'Repeat password'),
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     * @throws Exception
     */
    public function signup(): bool
    {
        if ($this->validate()) {
            $user = new User();
            $user->login = $this->username;
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
            $user->setAuthKey();
            $user->setApiKey();
            $user->save();
            return Yii::$app->user->login($user, 3600 * 24 * 30);
        }
        return false;
    }

}
