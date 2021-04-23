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

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model app\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('goip', 'Sign in');
?>

<div class="site-login">
    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-md-offset-4 col-md-4 col-sm-12">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-12'],
                ],
            ]); ?>
            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"col-lg-12\">{input} {label}</div>",
            ]) ?>
            <div class="form-group">
                <div class="col-md-12">
                    <?= Html::submitButton(Yii::t('goip', 'Login'), ['class' => 'btn waves-effect waves-light btn-success', 'name' => 'login-button', 'style' => 'display: block; width: 100%;']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="form-group">
                <div class="col-md-12">
                    <?= Html::a(Yii::t('goip', 'Register'), ['site/signup'], ['class' => 'btn waves-effect waves-light btn-primary', 'name' => 'login-button', 'style' => 'display: block; width: 100%;']) ?>
                </div>
            </div>
        </div>
    </div>
</div>