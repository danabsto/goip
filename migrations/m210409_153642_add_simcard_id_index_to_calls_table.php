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

use yii\db\Migration;

class m210409_153642_add_simcard_id_index_to_calls_table extends Migration
{
    public function safeUp()
    {
        $this->createIndex('calls_simcard_id_index', 'calls', 'simcard_id');
    }

    public function safeDown()
    {
        $this->dropIndex('calls_simcard_id_index', 'calls');
    }
}
