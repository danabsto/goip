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

namespace app\models\traits;

use Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Trait DropDownListTrait
 *
 * @property $dropDownId
 * @property $dropDownTitle
 * @property $dropDownFields
 */
trait DropDownListTrait
{
    protected static $_dropDownId = 'id';
    protected static $_dropDownTitle = 'title';
    protected static $_dropDownFields;

    /**
     * @param string[] $dropDownFields
     * @return array|ActiveRecord[]
     */
    public static function getDropDownList($dropDownFields = null): array
    {
        $select = [self::getDropDownId(), self::getDropDownTitle()];
        $dropDownFields = is_null($dropDownFields) ? self::getDropDownFields() : $dropDownFields;
        if (isset($dropDownFields)) {
            foreach ($dropDownFields as $key => $dropDownField) {
                $select[] = is_numeric($key) ? $dropDownField : $key;
            }
        }
        $devices = self::find()->select($select)->all();
        return ArrayHelper::map($devices, self::getDropDownId(), function ($device) use ($dropDownFields) {
            $result = $device->{self::getDropDownTitle()};
            if (isset($dropDownFields)) {
                $result .= ' ( ';
                $endField = end($dropDownFields);
                foreach (self::getDropDownFields() as $field) {
                    $result .= self::getElement($device, $field);
                    if ($endField != $field)
                        $result .= ', ';
                }
                $result .= ' )';
            }
            return $result;
        });
    }

    /**
     * @return string
     */
    protected static function getDropDownId(): string
    {
        return self::$_dropDownId;
    }

    /**
     * @return string
     */
    protected static function getDropDownTitle(): string
    {
        return self::$_dropDownTitle;
    }

    /**
     * @return array
     */
    protected static function getDropDownFields(): array
    {
        return self::$_dropDownFields;
    }

    /**
     * @param $element
     * @param $keys
     * @return mixed
     * @throws Exception
     */
    protected static function getElement($element, $keys)
    {
        if (is_string($keys)) {
            $keys = explode('.', $keys);
        }

        foreach ($keys as $key) {
            try {
                $element = $element->$key;
            } catch (Exception $e) {
                print_r([$element, $key]);
                exit();
            }
        }

        return $element;
    }
}