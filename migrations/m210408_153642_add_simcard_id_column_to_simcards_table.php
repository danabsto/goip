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

use app\models\Line;
use app\models\ShareMessages;
use yii\db\Migration;

class m210408_153642_add_simcard_id_column_to_simcards_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('share_messages', 'simcard_id', $this->integer() . ' NOT NULL');

        $shares = ShareMessages::find()->all();
        foreach ($shares as $share) {
            $line_id = $share->getAttribute('line_id');
            if (is_null($line_id)) continue;
            $line = Line::findOne(['id' => $line_id]);
            if (empty($line)) continue;
            $simcard_id = $line->getAttribute('simcard_id');
            if (is_null($simcard_id)) continue;
            $share->setAttribute('simcard_id', $simcard_id);
            $share->save(false);
        }

        $this->dropColumn('share_messages', 'line_id');
        $this->dropColumn('share_messages', 'device_id');
    }

    public function safeDown()
    {
        $this->addColumn('share_messages', 'device_id', $this->integer() . ' NOT NULL');
        $this->addColumn('share_messages', 'line_id', $this->integer() . ' NOT NULL');

        $shares = ShareMessages::find()->all();
        foreach ($shares as $share) {
            $simcard_id = $share->getAttribute('simcard_id');
            if (is_null($simcard_id)) continue;
            $line = Line::findOne(['simcard_id' => $simcard_id]);
            if (empty($line)) continue;
            $line_id = $line->getAttribute('simcard_id');
            if (is_null($line_id)) continue;
            $share->setAttribute('line_id', $line_id);
            $device_id = $line->getAttribute('simcard_id');
            if (is_null($device_id)) continue;
            $share->setAttribute('device_id', $device_id);
            $share->save(false);
        }

        $this->dropColumn('share_messages', 'simcard_id');
    }
}
