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

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Device */
/* @var $lines array */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goip', 'Devices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="devices-view">

    <h1><?= Yii::t("app", "Device"); ?> <?= Html::encode($this->title) ?></h1>

    <p><?= Html::a(Yii::t('goip', 'Update'),
            ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'host',
            'port',
            'login',
        ],
    ]) ?>
    <?= GridView::widget([
        "dataProvider" => new ArrayDataProvider(["allModels" => $model->getLines($model->user_id)->all()]),
        "columns" => [
            "number",
            "title",
            "simcard.phone",
            "simcard.operator.name",
            "imei",
            "imsi",
            "simcard.iccid"
        ]
    ]); ?>
</div>
