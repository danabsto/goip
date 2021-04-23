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

use app\components\goip\GoipApi;
use app\components\GoipUnavailableException;
use app\models\traits\DropDownListTrait;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "db_goip_devices".
 *
 * @property integer $id
 * @property string $title
 * @property string $host
 * @property string $port
 * @property string $login
 * @property string $password
 *
 * @property string $tm_activity
 * @property int $user_id [int]
 * @property int $weight [int]
 * @property bool $display_empty_lines [tinyint(1)]
 */
class Device extends \yii\db\ActiveRecord
{
    use DropDownListTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'devices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'host', 'port', 'login', 'password', 'display_empty_lines'], 'required'],
            ['weight', 'integer'],
            [['title', 'login', 'password'], 'string', 'max' => 255],
            ['host', 'ip'],
            ['port', 'integer', 'min' => 1, 'max' => 65535],
            ['tm_activity', 'safe'],
            [['host', 'port', 'login', 'password'], 'validateConnection'],
        ];
    }

    public function validateConnection($attribute, $params, $validator)
    {
        $goip = new GoipApi($this->host, $this->port, $this->login, $this->password);
        try {
            $goip->line->getInfo();
        } catch (GoipUnavailableException $e) {
            $this->addError($attribute);
            if (Yii::$app instanceof yii\web\Application)
                Yii::$app->session->setFlash('danger',
                    Yii::t('goip', 'WrongDeviceCredentials'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('goip', 'ID'),
            'title' => Yii::t('goip', 'Title'),
            'host' => Yii::t('goip', 'Host'),
            'port' => Yii::t('goip', 'Port'),
            'login' => Yii::t('goip', 'Username'),
            'password' => Yii::t('goip', 'Password'),
            'display_empty_lines' => Yii::t('goip', 'Display empty lines'),
            'weight' => Yii::t('goip', 'Device weight'),
        ];
    }

    /**
     * @param int|null $user_id
     */
    public function getLines(int $user_id = null)
    {
        $query = $this->hasMany(Line::class, ['device_id' => 'id']);
        if (isset($user_id)) {
            $query = $query->innerJoin('devices', 'device_lines.device_id = devices.id')
                ->leftJoin('share_messages',
                    ['and', 'share_messages.device_id = devices.id', ['share_messages.share_to' => $user_id]])
                ->leftJoin('share_message_simcard', 'share_messages.id = share_message_simcard.share_message_id')
                ->where(['devices.user_id' => $user_id])
                ->orWhere('device_lines.simcard_id = share_message_simcard.simcard_id')
                ->orderBy(['device_lines.number' => SORT_ASC]);
        }
        return $query;
    }

    /** получение от устройства информации о линиях и регистрация их в базе */
    public function registerLines(GoipApi $goipApi)
    {
        $lines = $goipApi->line->getInfo();
        foreach ($lines as $line) {
            $nl = new Line();
            $nl->device_id = $this->id;
            $nl->number = $line['id'];
            $nl->save(false);
        }
    }

    public function getLinesUsed()
    {
        return $this->getLines()->select(['number'])->indexBy('number')->asArray()->all();
    }

    public static function getLinesById($id, $freeOrAll = false)
    {
        $device = self::findOne($id);

        $lines = range(1, $device->count_lines);
        if ($freeOrAll) {
            $linesUsed = $device->getLinesUsed();
            $lines = array_filter($lines, function ($line) use ($linesUsed) {
                return !isset($linesUsed[$line]);
            });
        }

        return $lines;
    }

    /**
     * Массив всех линий устройства
     * @param $id ид устройства
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllLinesArray($id)
    {
        return Line::find()->where(['device_id' => $id])->with('simcard')->asArray()->all();
    }

    /**
     * @param integer $id
     * @param boolean $isJs
     * @param array $excludeNumbers
     * @return array
     */
    public static function getDropDownLines($id, $isJs = false, $excludeNumbers = null)
    {
        $result = [];
        $lines = self::getLinesById($id, true);

        if (!empty($excludeNumbers)) {
            $lines = array_merge($excludeNumbers, $lines);
        }

        foreach ($lines as $iLine) {
            if ($isJs) {
                $result[] = ['id' => $iLine, 'name' => $iLine];
            } else {
                $result[$iLine] = $iLine;
            }
        }
        return $result;
    }

    public static function getDevicesQuery(int $user_id = null, bool $with_shared = false)
    {
        $query = Device::find();
        if (!empty($user_id)) {
            $query = $query->leftJoin('device_weights',
                ['and', 'device_weights.device_id = devices.id', ['device_weights.user_id' => $user_id]]);
            $query = $query->where(['devices.user_id' => $user_id]);
            if ($with_shared) {
                $query = $query->joinWith('shares');
                $query = $query->orWhere(['share_messages.share_to' => $user_id]);
            }
            $query = $query->groupBy(['devices.id', 'device_weights.weight']);
            $query = $query->orderBy(['device_weights.weight' => SORT_ASC]);
        }
        return $query;
    }

    public static function getDevices(int $user_id = null, bool $with_shared = false)
    {
        return self::getDevicesQuery($user_id, $with_shared)->all();
    }

    public static function getShared($user_id)
    {
        return Device::find()
            ->innerJoinWith('messages')
            ->where(['share_to' => $user_id])
            ->all();
    }

    public function getShares()
    {
        return $this->hasMany(ShareMessages::className(), ['device_id' => 'id']);
    }

    public function afterDelete()
    {
        foreach ($this->lines as $line) $line->delete();
        parent::afterDelete();
    }

    public function hasShared()
    {
        $lines = $this->getLines($this->user_id)->all();
        if (empty($lines)) {
            return false;
        }
        foreach ($lines as $line) {
            if ($line->isShared()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $device_id
     * @return Device
     * @throws UserException
     */
    public static function findDevice(int $device_id): Device
    {
        $device = Device::findOne(['id' => $device_id, 'user_id' => Yii::$app->user->id]);
        if (empty($device)) {
            throw new UserException('Device not found or you don\'t have access to this device');
        }
        return $device;
    }

    /**
     * @return ActiveQuery
     */
    public function getWeights(): ActiveQuery
    {
        return $this->hasMany(DeviceWeight::class, ['device_id' => 'id']);
    }

    /**
     * @return DeviceWeight|null
     */
    private function findWeight(): ?DeviceWeight
    {
        $user_id = Yii::$app instanceof yii\web\Application ? Yii::$app->user->id : $this->id;
        return DeviceWeight::findOne(['device_id' => $this->id, 'user_id' => $user_id]);
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        $device_weight = $this->findWeight();
        return (int)!empty($device_weight)
            ? $device_weight->getAttribute('weight')
            : DeviceWeight::DEFAULT_WEIGHT;
    }

    /**
     * @param int $weight
     * @return bool
     */
    public function setWeight(int $weight): bool
    {
        $user_id = Yii::$app instanceof yii\web\Application ? Yii::$app->user->id : $this->id;
        $device_weight = $this->findWeight();
        if (empty($device_weight)) {
            $device_weight = new DeviceWeight([
                'device_id' => $this->id,
                'user_id' => $user_id
            ]);
        }
        $device_weight->weight = $weight;
        return $device_weight->save();
    }
}
