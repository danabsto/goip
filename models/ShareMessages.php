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

use dektrium\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "share_messages".
 *
 * @property integer $id
 * @property integer $device_id
 * @property integer $line_id
 * @property integer $user_id
 * @property integer $share_to
 * @property string $filters
 *
 * @property string $tm_updated
 * @property mixed|null simcard
 * @property int $simcard_id [int]
 */
class ShareMessages extends ActiveRecord
{
    public $comparison_condition = '';
    public $share_to_email = '';
    public $simcard_ids = [];

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'share_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['comparison_condition', 'string'],
            ['share_to_email', 'required'],
            ['share_to_email', 'email'],
            ['share_to_email', 'validateEmail'],
            ['tm_updated', 'safe'],
            ['simcard_ids', 'required'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validateEmail($attribute, $params, $validator)
    {
        $user = User::findOne(['email' => $this->$attribute]);
        if (!isset($user))
            $this->addError($attribute, Yii::t('goip', 'User with email «{email}» not found.',
                ['email' => $this->$attribute]));
        $current_user = User::findOne(['id' => Yii::$app->user->id]);
        if ($this->$attribute == $current_user->email)
            $this->addError($attribute, Yii::t('goip', 'You can not share devices with yourself.',
                ['email' => $this->$attribute]));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('goip', 'ID'),
            'tm_updated' => Yii::t('goip', 'Updated'),
            'filters' => Yii::t('goip', 'messages_filters'),
            'or' => Yii::t('goip', 'matches at least one of conditions'),
            'and' => Yii::t('goip', 'matches all the conditions'),
            'share_to_email' => Yii::t('goip', 'Shared to'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if (!empty($this->share_to_email)) {
            $user_email = $this->share_to_email;
            $user = User::findOne(['email' => $user_email]);
            $this->share_to = $user->id;
        }
        if (!Yii::$app instanceof Yii\console\Application)
            $this->filters = json_encode([
                'comparison_condition' => Yii::$app->request->post('ShareMessages')['comparison_condition'],
                'conditions' => Yii::$app->request->post('ShareCondition'),
            ]);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->unlinkAll('simcards', true);
        foreach ($this->simcard_ids as $simcard_id) {
            $simcard = Simcard::findOne(['id' => $simcard_id]);
            $this->link('simcards', $simcard);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        $this->unlinkAll('simcards', true);
        return parent::beforeDelete();
    }

    /**
     * @return ActiveQuery
     */
    public function getLines(): ActiveQuery
    {
        return $this->hasMany(Line::class, ['simcard_id' => 'id'])
            ->via('simcards');
    }

    /**
     * @return ActiveQuery
     */
    public function getOwner(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'share_to']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSimcard(): ActiveQuery
    {
        return $this->hasOne(Simcard::class, ['id' => 'simcard_id']);
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getSimcards(): ActiveQuery
    {
        return $this->hasMany(Simcard::class, ['id' => 'simcard_id'])
            ->viaTable('share_message_simcard', ['share_message_id' => 'id']);
    }
}
