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

use app\models\ShareMessages;
use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m210414_111322_add_device_id_column_to_share_messages_table
 */
class m210414_111322_add_device_id_column_to_share_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('share_messages', 'device_id', Schema::TYPE_INTEGER . ' NOT NULL');

        $shares = ShareMessages::find()->all();
        foreach ($shares as $share) {
            $simcards = $share->simcards;
            if (empty($simcards)) continue;
            $line = $simcards[0]->line;
            if (empty($line)) continue;
            $device = $line->device;
            if (empty($device)) continue;
            $share->setAttribute('device_id', $device->getAttribute('id'));
            $share->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('share_messages', 'device_id');
    }
}
