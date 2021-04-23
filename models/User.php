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

use dektrium\user\models\User as BaseUser;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;


class User extends BaseUser
{
    /**
     * @param $user
     * @return mixed
     */
    public static function isUserAdmin($user)
    {
        return $user->getIsAdmin();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return User|null
     */
    public static function findByUsername(string $username): ?User
    {
        return self::find()->where(['username' => mb_strtolower($username)])->one();
    }

    /**
     * @param $key
     * @return array|ActiveRecord|null
     */
    public static function findUserByApiKey($key)
    {
        return self::find()->where(['=', 'api_key', $key])->one();
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return IdentityInterface|User|null
     * @throws ForbiddenHttpException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $identity = static::findOne(['api_key' => $token]);
        if ($identity->isBlocked)
            throw new ForbiddenHttpException(Yii::t('goip', 'API access forbidden: User blocked.'));
        return $identity;
    }

    /**
     * @return array
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios['create'][] = 'api_key';
        $scenarios['update'][] = 'api_key';
        $scenarios['register'][] = 'api_key';
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['fieldLength'] = ['api_key', 'string', 'min' => 10, 'max' => 32];
        $rules['apikeyUnique'] = ['api_key', 'unique'];
        return $rules;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function beforeSave($insert): bool
    {
        if ($insert and empty($this->api_key))
            $this->api_key = md5(Yii::$app->getSecurity()->generateRandomString());
        return parent::beforeSave($insert);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->getIsAdmin();
    }

    /**
     * @return array|mixed|null
     * @throws InvalidConfigException
     */
    public function getLinesArray()
    {
        if ($this->lines_array === null)
            $this->lines_array = ArrayHelper::map($this->getLines()->asArray()->all(), 'id', 'number');
        return $this->lines_array;
    }

    /**
     * user's lines
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getLines(): ActiveQuery
    {
        return $this->hasMany(Line::class, ['id' => 'line_id'])
            ->viaTable('user_lines', ['user_id' => 'id']);
    }

    /**
     * @throws Exception
     */
    public function setApiKey()
    {
        $this->apikey = Yii::$app->security->generateRandomString(8);
    }

    /**
     * @return ActiveQuery
     */
    public function getDevices(): ActiveQuery
    {
        return $this->hasMany(Device::className(), ['devices.user_id' => 'id']);
    }
}
