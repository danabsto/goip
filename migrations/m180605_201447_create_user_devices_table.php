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
 * Handles the creation of table `user_devices`.
 */
class m180605_201447_create_user_devices_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user_devices', [
            'id'        => $this->primaryKey(),
            'user_id'   => $this->integer(),
            'token'     => $this->string(),
            'model'     => $this->string(),
            'type'      => $this->smallInteger(),
            'status'    => $this->boolean()->defaultValue(true),
            'tm_create' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('user_devices');
    }
}
