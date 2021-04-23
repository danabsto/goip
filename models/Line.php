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
use yii\helpers\Url;

/**
 *
 * @property integer $id
 * @property integer $number
 * @property integer $device_id
 * @property string $imei
 * @property string $imsi
 * @property integer $simcard_id
 * @property string $forward
 *
 * @property Device $device
 * @property Message[] $messages
 * @property Call[] $calls
 * @property Simcard $simcard
 */
class Line extends ActiveRecord
{
    use DropDownListTrait;

    protected static $dropDownFields = ['device_id' => 'device.title'];

    const LIST_LAST_RELATION = ['messages', 'ussd', 'dial'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device_lines';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'device_id', 'imei', 'imsi'], 'required'],
            [['number', 'device_id', 'simcard_id'], 'integer'],
            [['title', 'imei', 'imsi'], 'string', 'max' => 255],
            ['forward', 'string', 'max' => 16],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        ];
    }

    /**
     * @return string
     */
    public function getFullTitle(): string
    {
        return !empty($this->title) ? $this->title : Yii::t('goip', 'Line {number}', ['number' => $this->number]);
    }

    /**
     * @return string|null
     */
    public function getFullTitleUrl(): ?string
    {
        return !empty($this->simcard) ? Url::to(['messages/index', 'id' => $this->simcard->id]) : null;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('goip', 'ID'),
            'title' => Yii::t('goip', 'Title'),
            'number' => Yii::t('goip', 'Number'),
            'phone' => Yii::t('goip', 'Phone Number'),
            'operator_id' => Yii::t('goip', 'Operator'),
            'device_id' => Yii::t('goip', 'Device'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getSimcard()
    {
        return $this->hasOne(Simcard::className(), ["id" => "simcard_id"]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['simcard_id' => 'simcard_id'])->orderBy(['tm' => SORT_DESC]);
    }

    /**
     * @return array
     */
    public function getMessagesBySender($sender)
    {
        return $this->hasMany(Message::className(), ['simcard_id' => 'simcard_id'])->andOnCondition(['address' => $sender])->orderBy(['tm' => SORT_DESC])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalls()
    {
        return $this->hasMany(Call::className(), ['simcard_id' => 'simcard_id'])->orderBy(['tm' => SORT_DESC]);
    }

    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }

        $simcard = $this->simcard;
        if (isset($simcard)) {
            $simcard->delete();
        }

        return true;
    }

    /**
     * Method for getting the last message
     *
     * @return Line|array|ActiveRecord|null
     */
    public function getMessage()
    {
        return $this->hasMany(Message::className(), ['simcard_id' => 'simcard_id'])
            ->orderBy(['tm' => SORT_DESC])
            ->one();
    }

    /**
     * Method for getting the last call
     *
     * @return Line|array|ActiveRecord|null
     */
    public function getCall()
    {
        return $this->hasMany(Call::className(), ['simcard_id' => 'simcard_id'])
            ->orderBy(['tm' => SORT_DESC])
            ->one();
    }

    public static function getLinesQuery($user_id = null, $with_shared = false, $device_id = null)
    {
        $query = Line::find();
        if (!is_null($user_id))
            $query = $query->innerJoin('devices', 'devices.id = device_lines.device_id');
        if ($with_shared)
            $query = $query->leftJoin('share_messages', 'share_messages.device_id = devices.id')
                ->leftJoin('share_message_simcard', 'share_message_simcard.share_message_id = share_messages.id')
                ->leftJoin('simcards', 'simcards.id = share_message_simcard.simcard_id')
                ->where(['share_messages.share_to' => $user_id])
                ->andWhere('simcards.id = device_lines.simcard_id');
        if (!is_null($user_id))
            $query = $with_shared
                ? $query->orWhere(['devices.user_id' => $user_id])
                : $query->where(['devices.user_id' => $user_id]);
        if (!is_null($device_id))
            $query = ($with_shared or !is_null($user_id))
                ? $query->andWhere(['device_lines.device_id' => $device_id])
                : $query->where(['device_lines.device_id' => $device_id]);
        return $query->groupBy('device_lines.id');
    }

    public static function getLines($user_id = null, $with_shared = false, $device_id = null)
    {
        return self::getLinesQuery($user_id, $with_shared, $device_id)->all();
    }

    public function getLastMessageTm() {
        return Message::find()
            ->select('tm_create')
            ->where(['simcard_id' => $this->simcard_id])
            ->orderBy(['tm_create' => SORT_DESC])
            ->limit(1);
    }

    public function getLastCallTm() {
        return Call::find()
            ->select('tm')
            ->where(['simcard_id' => $this->simcard_id])
            ->orderBy(['tm' => SORT_DESC])
            ->limit(1);
    }

    public function getShouldBeDisplayed() {
        if (empty($this->simcard) and !$this->device->display_empty_lines) {
            return false;
        }
        return true;
    }
}
