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

/**
 * Handles the creation of table `ims`.
 */
class m210324_162200_create_ims_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up ()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable("ims", [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->defaultValue(null),
            'service' => $this->string()->notNull(),
            'user_id' => $this->string()->notNull(),
            'name' => $this->string(),
            'active' => $this->boolean()->defaultValue(false),
            'tm_created' => $this->dateTime(),
            'vcode' => $this->string(),
            'settings' => $this->text()
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down ()
    {
        $this->dropTable('ims');
    }
}
