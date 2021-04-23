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
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "simcards".
 *
 * @property integer $id
 * @property integer $phone
 * @property string $iccid
 * @property string $balance
 * @property integer $operator_id
 * @property Line line
 * @property Operator $operator
 * @property Balance $yesterdayBalance
 * @property Balance $weekBalance
 * @property Balance $monthBalance
 * @property Message[] messages
 * @property Call[] calls
 * @property ShareMessages[] shares
 * @property int $user_id [int]
 */
class Simcard extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'simcards';
    }

    /**
     * @param int $device_id
     * @return array
     */
    public static function getSharedSimcards(int $device_id): array
    {
        $simcard_models = Simcard::find()
            ->innerJoinWith('line')
            ->where(['device_id' => $device_id])
            ->all();
        $simcards = [];
        foreach ($simcard_models as $simcard_model)
            $simcards = ArrayHelper::merge($simcards, [
                $simcard_model->id => self::getSimcardTitle($simcard_model)
            ]);
        return $simcards;
    }

    /**
     * @param $simcard_model
     * @return string
     */
    public static function getSimcardTitle($simcard_model): string
    {
        $line_number = $simcard_model->line->number;
        $line_title = !empty($simcard_model->line->title) ? $simcard_model->line->title : Yii::t('goip', 'Untitled line');
        $simcard_phone = !empty($simcard_model->phone) ? $simcard_model->phone : Yii::t('goip', 'missing phone number');
        return "$line_number. $line_title ($simcard_phone)";
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['phone', 'operator_id'], 'integer'],
            [['balance'], 'number'],
            [['iccid'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'iccid' => 'Iccid',
            'balance' => 'Balance',
            'operator_id' => 'Operator ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getLine(): ActiveQuery
    {
        return $this->hasOne(Line::className(), ["simcard_id" => "id"]);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator(): ActiveQuery
    {
        return $this->hasOne(Operator::className(), ["id" => "operator_id"]);
    }

    /**
     * @return int|string
     */
    public function getPhoneText()
    {
        if ($this->phone == "") return "Номер не указан";
        return $this->phone;
    }

    /**
     * @return ActiveQuery
     */
    public function getYesterdayBalance(): ActiveQuery
    {
        return $this->hasOne(Balance::class, ["simcard_id" => "id"])
            ->where(new Expression("tm >= NOW() - INTERVAL 1 day"))
            ->orderBy(["tm" => SORT_ASC])
            ->limit(1);
    }

    /**
     * @return ActiveQuery
     */
    public function getWeekBalance(): ActiveQuery
    {
        return $this->hasOne(Balance::class, ["simcard_id" => "id"])
            ->where(new Expression("tm >= NOW() - INTERVAL 1 week"))
            ->orderBy(["tm" => SORT_ASC])
            ->limit(1);
    }

    public function getMonthBalance(): ActiveQuery
    {
        return $this->hasOne(Balance::class, ["simcard_id" => "id"])
            ->where(new Expression("tm >= NOW() - INTERVAL 1 month"))
            ->orderBy(["tm" => SORT_ASC])
            ->limit(1);
    }

    /**
     * @return ActiveQuery
     */
    public function getBalance(): ActiveQuery
    {
        return $this->hasOne(Balance::class, ['simcard_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBalances(): ActiveQuery
    {
        return $this->hasMany(Balance::class, ['simcard_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMessages(): ActiveQuery
    {
        return $this->hasMany(Message::class, ['simcard_id' => 'id'])->orderBy(['tm' => SORT_DESC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCalls(): ActiveQuery
    {
        return $this->hasMany(Call::className(), ['simcard_id' => 'id'])->orderBy(['tm' => SORT_DESC]);
    }

    /**
     * @param null $number
     * @return Simcard|array|ActiveRecord|null
     */
    public function getLastCall($number = null)
    {
        $result = $this->hasMany(Call::className(), ['simcard_id' => 'id']);
        if (isset($number)) $result = $result->where(['phone' => $number]);
        $result = $result->orderBy(['tm' => SORT_DESC]);
        return $result->one();
    }

    /**
     * @inheritdoc
     */
    public function delete(): bool
    {
        if (!parent::delete()) return false;
        $balance = $this->balance;
        if (isset($balance)) $balance->delete();
        $messages = $this->messages;
        if (isset($messages))
            foreach ($messages as $message)
                $message->delete();
        $calls = $this->calls;
        if (isset($calls))
            foreach ($calls as $call)
                $call->delete();
        return true;
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getShares(): ActiveQuery
    {
        return $this->hasMany(ShareMessages::class, ['id' => 'share_message_id'])
            ->viaTable('share_message_simcard', ['simcard_id' => 'id'])
            ->groupBy(['id']);
    }
}
