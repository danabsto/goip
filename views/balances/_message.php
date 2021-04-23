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

/** @var array $message */

echo Yii::t('goip', 'За посление {check_balance_interval} были следующие изменения балансов', [
        'check_balance_interval' => Yii::$app->formatter->asDuration($message['last_activity']),
    ]) . PHP_EOL . PHP_EOL;
$i = 1;
foreach ($message['devices'] as $device_title => $lines):
    foreach ($lines as $line_number => $line):
        echo Yii::t('goip', '{i}) Устройство "{device_title}", в слоте №{line_number} симкарта "{line_title}" было списание на {line_balance_diff} ₽', [
                'i' => $i,
                'device_title' => $device_title,
                'line_number' => $line_number,
                'line_title' => $line['title'],
                'line_balance_diff' => $line['balance_diff']
            ]) . PHP_EOL;
        $i += 1;
    endforeach;
    echo PHP_EOL;
endforeach;
