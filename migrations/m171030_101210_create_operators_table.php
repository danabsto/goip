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
 * Handles the creation of table `operators`.
 */
class m171030_101210_create_operators_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable("operators", [
            'id'                    => $this->primaryKey(),
            'name'                  => $this->string()->unique()->notNull(),
            'get_balance_ussd'      => $this->string(),
            'get_phone_ussd'        => $this->string(),
            'get_balance_regexp'    => $this->string(),
            'get_phone_regexp'      => $this->string(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('operators');
    }
}
