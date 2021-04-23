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

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Settings */
/* @var $settings array */

$this->title = Yii::t('goip', 'Update {modelClass}: ', ['modelClass' => 'Settings']) . $settings[$model->name]['label'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('goip', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('goip', $settings[$model->name]['label']), 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('goip', 'Update');
?>

<div class="settings-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'settings' => $settings,
    ]) ?>
</div>
