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

use app\components\Alert;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\Device $model
 */

$this->title = Yii::t('goip', 'Device {0} reboot', [$model->title]);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu', ['model' => $model]); ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?= Alert::widget() ?>
                <?php $form = ActiveForm::begin(); ?>
                <?= Html::submitButton(Yii::t('goip', 'Reboot device'), ['class' => 'btn btn-danger',
                    'data-confirm' => Yii::t('goip', 'Are you sure you want to reboot this device?')]) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
