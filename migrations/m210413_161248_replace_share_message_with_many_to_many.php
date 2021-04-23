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
use app\models\ShareSimcard;
use yii\db\Migration;
use yii\db\Schema;

class m210413_161248_replace_share_message_with_many_to_many extends Migration
{
    public function safeUp()
    {
        if (!$this->getDb()->getTableSchema('share_message_simcard')) {
            $this->createTable('share_message_simcard', [
                'id' => Schema::TYPE_PK,
                'share_message_id' => Schema::TYPE_INTEGER,
                'simcard_id' => Schema::TYPE_INTEGER,
            ]);
            $this->createIndex('share_simcard_share_message_id_index', 'share_message_simcard', 'share_message_id');
            $this->createIndex('share_simcard_simcard_id_index', 'share_message_simcard', 'simcard_id');
        }

        $share_messages = ShareMessages::find()->all();
        foreach ($share_messages as $share_message) {
            $share_simcard = new ShareSimcard;
            $share_simcard->setAttribute('share_message_id', $share_message->getAttribute('id'));
            $share_simcard->setAttribute('simcard_id', $share_message->getAttribute('simcard_id'));
            $share_simcard->save();
        }

//        $this->dropColumn('share_messages_simcard_id_index', 'share_messages');
        if (!$this->getDb()->getTableSchema('share_message_simcard')->getColumn('share_messages'))
            $this->dropColumn('share_messages', 'simcard_id');
    }

    public function safeDown()
    {
        if (!$this->getDb()->getTableSchema('share_message_simcard')->getColumn('share_messages'))
            $this->addColumn('share_messages', 'simcard_id', Schema::TYPE_INTEGER . ' NOT NULL');

        if ($this->getDb()->getTableSchema('share_message_simcard')) {
            $this->dropIndex('share_simcard_share_message_id_index', 'share_message_simcard');
            $this->dropIndex('share_simcard_simcard_id_index', 'share_message_simcard');
            $this->dropTable('share_message_simcard');
        }
    }
}
