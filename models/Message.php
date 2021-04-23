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
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%goip_messages}}".
 *
 * @property integer $id
 * @property integer $simcard_id
 * @property string $text
 * @property integer $type
 * @property integer $tm_send
 * @property integer $tm_create
 * @property string $address
 * @property integer $status
 *
 * @property Simcard $simcard
 * @property false|string tm
 */
class Message extends ActiveRecord
{

    const TYPE_INCOMING = 0;
    const TYPE_OUTGOING = 1;

    protected static $listType = null;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'messages';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['simcard_id', 'text', 'address'], 'required'],
            [['status'], 'integer'],
            [['text'], 'string', 'max' => 2048],
            [['address'], 'string'],
            [['status'], 'default', 'value' => 0],
            [['type'], 'default', 'value' => 1],
            [['simcard_id'], 'exist', 'skipOnError' => true, 'targetClass' => Simcard::className(), 'targetAttribute' => ['simcard_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('goip', 'ID'),
            'line_id' => Yii::t('goip', 'Line'),
            'text' => Yii::t('goip', 'Text'),
            'type' => Yii::t('goip', 'Type'),
            'sent_at' => Yii::t('goip', 'Sent At'),
            'created_at' => Yii::t('goip', 'Created at'),
            'companion' => Yii::t('goip', 'Companion'),
            'status' => Yii::t('goip', 'Status'),
            'status_message' => Yii::t('goip', 'Status message'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getSimcard(): ActiveQuery
    {
        return $this->hasOne(Simcard::className(), ["id" => "simcard_id"]);
    }

    /**
     * @return ActiveQuery
     */
    public function getLine(): ActiveQuery
    {
        return $this->hasOne(Line::class, ['simcard_id' => 'simcard_id']);
    }

}
