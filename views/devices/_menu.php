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

use yii\widgets\Menu;

/**
 * @var app\models\Device $model
 */

$menu_items = [];

if (Yii::$app->user->id == $model->user_id)
    $menu_items[] = ['label' => Yii::t('goip', 'Connection'), 'url' => ['/devices/update', 'id' => $model->id]];
$menu_items[] = ['label' => Yii::t('goip', 'Display settings'), 'url' => ['/devices/settings', 'id' => $model->id]];
if (Yii::$app->user->id == $model->user_id) {
    $menu_items[] = ['label' => Yii::t('goip', 'Access/sharing settings'), 'url' => ['/share/device', 'id' => $model->id]];
    $menu_items[] = ['label' => Yii::t('goip', 'Reboot'), 'url' => ['/devices/reboot', 'id' => $model->id]];
}

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= Yii::t('goip', "{0} device", [$model->title]) ?>
        </h3>
    </div>
    <div class="panel-body">
        <?= Menu::widget([
            'options' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
            'items' => $menu_items,
        ]); ?>
    </div>
</div>
