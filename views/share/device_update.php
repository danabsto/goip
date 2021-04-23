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
/* @var $simcards app\models\Simcard[] */
/* @var $device app\models\Device */
/* @var $share app\models\ShareMessages */
/* @var $shareConditions app\models\ShareMessages */

$this->title = Yii::t('goip', 'Create share for device - ') . $device->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goip', 'Share'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="settings-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_device_form', [
        'simcards' => $simcards,
        'share' => $share,
        'shareConditions' => $shareConditions,
    ]) ?>
</div>
