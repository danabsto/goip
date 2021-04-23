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

use app\models\Message;
use yii\db\Migration;

class m180315_065935_add_tm_to_messages extends Migration
{
    public function safeUp()
    {
        $this->addColumn(Message::tableName(), "tm", $this->dateTime());
    }

    public function safeDown()
    {
        echo "m180315_065935_add_tm_to_messages cannot be reverted.\n";

        return false;
    }
}
