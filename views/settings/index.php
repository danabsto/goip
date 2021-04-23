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

use app\models\Settings;
use yii\helpers\Html;
use yii\grid\GridView;
use \yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('goip', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu'); ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <div class="settings-index">
                    <h1><?= Html::encode($this->title) ?></h1>
                    <?php if (!empty(Settings::getAvailableDefaultSettings())): ?>
                        <p><?= Html::a(Yii::t('goip', 'Create setting'), ['create'], ['class' => 'btn btn-success']) ?></p>
                    <?php endif; ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'attribute' => 'name',
                                'value' => function ($model) {
                                    return Settings::getDefaultSettings()[$model->name]['label'];
                                }
                            ],
                            [
                                'attribute' => 'value',
                                'value' => function ($model) {
                                    $setting = Settings::getDefaultSettings()[$model->name];
                                    if (isset($setting['value']) && isset($setting['value'][$model->value])) {
                                        return $setting['value'][$model->value];
                                    }
                                    return $model->value;
                                }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{update}{delete}',
                                'urlCreator' => function ($action, $model, $key, $index) {

                                    if ($action === 'update') {
                                        return '/settings/update?id=' . $model->name;
                                    }
                                    if ($action === 'delete') {
                                        return '/settings/delete?id=' . $model->name;
                                    }

                                }
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
