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

use app\models\Simcard;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Line */
/* @var $devices array */
/* @var $lines array */
/* @var $operators array */
/* @var $checksDataProvider ActiveDataProvider */
/* @var $simcard Simcard */

$this->title = Yii::t('goip', 'Update {modelClass}: ', ['modelClass' => 'Lines']) . $model->getFullTitle();
$this->params['breadcrumbs'][] = ['label' => Yii::t('goip', 'Lines'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goip', 'Update');
?>
<div class="lines-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'simcard' => $simcard
    ]) ?>
</div>
