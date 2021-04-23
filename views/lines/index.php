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
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var  array $operatorsDD */

$this->title = Yii::t('goip', 'Lines');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lines-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::a(Yii::t('goip', 'Create lines'), ['create'], ['class' => 'btn btn-success']) ?></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'columns' => [
            [
                'attribute' => 'number',
                'headerOptions' => ['class' => 'short-3sign']
            ],
            'title',
            'phone_number',
            [
                'label' => Yii::t('goip', 'Device'),
                'attribute' => 'device',
                'value' => 'device.title',
            ],
            [
                'attribute' => 'operator_id',
                'value' => function (Line $line) {
                    return ArrayHelper::getValue($line, ["operator", "title"]);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}'
            ],
        ],
    ]); ?>
</div>
