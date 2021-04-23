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

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('goip', 'Devices');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="devices-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::a(Yii::t('goip', 'Create device'), ['create'], ['class' => 'btn btn-success']) ?></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'title',
            'host',
            'port',
            'login',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url) {
                        return Html::a(Yii::t('goip', 'Lines'), $url, ["class" => 'btn btn-xs btn-warning']);
                    },
                    'update' => function ($url) {
                        return Html::a(Yii::t('goip', 'Update'), $url, ["class" => 'btn btn-xs btn-primary']);
                    },
                    'delete' => function ($url) {
                        return Html::a(Yii::t('goip', 'Delete'), $url, [
                            'class' => 'btn btn-xs btn-danger',
                            'data-method' => 'POST',
                            'data-confirm' => Yii::t('goip', 'Delete message?'),
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>

</div>
