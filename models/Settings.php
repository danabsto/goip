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
 * This is the model class for table "settings".
 *
 * @property string $name
 * @property string $value
 * @property integer $user_id
 * @property int $id [int]
 */
class Settings extends ActiveRecord
{
    protected static $defaultSettings;
    protected static $availableDefaultSettings;
    protected static $_settings = [];

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'settings';
    }

    public static function byName($name, $update = false, $create = false, $defaultValue = null)
    {
        if (!isset(self::$_settings[$name]) || $update) {
            $setting = self::find()->where(['name' => $name])->one();
            if ($create && empty($setting)) {
                if (is_null($defaultValue)) $defaultValue = self::getDefaultSettings()[$name]['defaultValue'];
                $setting = new static();
                $setting->setAttributes([
                    'name' => $name,
                    'value' => (string)$defaultValue,
                    'user_id' => Yii::$app->user->id,
                ]);
                $setting->save();
            }
            self::$_settings[$name] = $setting;
        }
        return self::$_settings[$name];
    }

    /**
     * @return ActiveQuery
     */
    public static function find(): ActiveQuery
    {
        return parent::find()->where(['NOT IN', 'name', ['sms_inbox_last']]);
    }

    /**
     * @return array[]
     */
    public static function getDefaultSettings(): array
    {
        if (is_null(self::$defaultSettings)) {
            $hour = 60 * 60;
            $day = 24 * $hour;
            $yiiMessage = function ($time, $step) {
                return $time == 1 ? Yii::t('goip', 'Ever a {step}', [
                    'step' => $step
                ]) : Yii::t('goip', 'Ever a {time} {step}', [
                    'time' => Yii::t('goip', $time),
                    'step' => $step
                ]);
            };
            $defaultSettings = [
                [
                    'name' => 'check_balance',
                    'label' => Yii::t('goip', 'Check balance'),
                    'value' => [
                        6 * $hour => $yiiMessage(6, Yii::t('goip', 'hour')),
                        12 * $hour => $yiiMessage(12, Yii::t('goip', 'hours')),
                        24 * $hour => $yiiMessage(1, Yii::t('goip', 'day'))
                    ],
                    'defaultValue' => 12 * $hour,
                ],
                [
                    'name' => 'alert_sms_expired',
                    'label' => Yii::t('goip', 'Alert time sms'),
                    'value' => [
                        1 * $hour => $yiiMessage(1, Yii::t('goip', 'hour')),
                        1 * $day => $yiiMessage(1, Yii::t('goip', 'day')),
                        7 * $day => $yiiMessage(7, Yii::t('goip', 'days'))
                    ],
                    'defaultValue' => 1 * $day,
                ],
                [
                    'name' => 'dial_test',
                    'label' => Yii::t('goip', 'Dial test'),
                    'value' => [
                        0 => Yii::t('goip', 'Never'),
                        1 * $day => $yiiMessage(1, Yii::t('goip', 'day')),
                        7 * $day => $yiiMessage(7, Yii::t('goip', 'days')),
                        30 * $day => $yiiMessage(30, Yii::t('goip', 'days'))],
                    'defaultValue' => 7 * $day,
                ],
                [
                    'name' => 'dial_test_number',
                    'label' => Yii::t('goip', 'Dial test number'),
                    'defaultValue' => "+74957222666",
                ],
            ];
            foreach ($defaultSettings as $iDefaultSettings => $defaultSetting) {
                unset($defaultSettings[$iDefaultSettings]);
                $defaultSettings[$defaultSetting['name']] = $defaultSetting;
            }
            self::$defaultSettings = $defaultSettings;
        }
        return self::$defaultSettings;
    }

    /**
     * @param null|Settings $model
     * @param bool $update
     * @return array
     */
    public static function getAvailableDefaultSettings($model = null, $update = false): array
    {
        if (is_null(self::$availableDefaultSettings) or $update) {
            $defaultSettings = self::getDefaultSettings();
            $settings = Settings::find()->where(['user_id' => Yii::$app->user->id])->indexBy('name')->all();
            foreach ($defaultSettings as $iDefaultSetting => $defaultSetting)
                if (!empty($settings[$defaultSetting['name']]))
                    unset($defaultSettings[$iDefaultSetting]);
            if (isset($model) and isset($model->name)) {
                $currentSetting = self::getDefaultSettings()[$model->name];
                $defaultSettings[$model->name] = $currentSetting;
            }
            self::$availableDefaultSettings = $defaultSettings;
        }
        return self::$availableDefaultSettings;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'value'], 'required'],
            [['name', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('goip', 'Name'),
            'value' => Yii::t('goip', 'Value'),
        ];
    }
}
