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

/* @var $this View */
/* @var $items array */

use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('goip', 'Bulk SMS sending');
?>

<div class="messages-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if ($success = Yii::$app->session->getFlash("success")): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?= Html::beginForm(); ?>
    <div class="form-group">
        <label for="line"><?php echo Yii::t('goip', 'Select line'); ?></label>
        <?= Html::dropDownList("line", "", $items, ["class" => "form-control", "prompt" => Yii::t('goip', 'Select line')]); ?>
    </div>
    <div class="form-group">
        <label for="messages"><?php echo Yii::t('goip', 'List of numbers and messages'); ?></label>
        <?= Html::textarea("messages", "", ["rows" => 20, "class" => "form-control"]); ?>
    </div>
    <?= Html::submitButton(Yii::t('goip', 'Send'), ["class" => "btn waves-effect waves-light btn-success"]); ?>
    <?= Html::endForm(); ?>
</div>