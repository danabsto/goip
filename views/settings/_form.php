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
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Settings */
/* @var $form yii\widgets\ActiveForm */
/* @var $settings array */
?>

<div class="settings-form">
    <?php $form = ActiveForm::begin(['id' => 'settings-form']); ?>
    <?= $form->field($model, 'name')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(Settings::getAvailableDefaultSettings(), 'name', 'label'),
        'options' => [
            'onchange' => '$.pjax.submit({currentTarget: document.getElementById("settings-form"), preventDefault: function(){}}, "#setting_input")'//'$.pjax.reload({container:"#setting_input", "type":"POST", "url":"' . Url::to(['', 'name' => '']) . '"+this.value});'
        ]
    ]);
    ?>
    <?php
    Pjax::begin(['id' => 'setting_input', 'enablePushState' => false, 'enableReplaceState' => false]);
    if (isset($settings[$model->name]['value'])) {
        echo $form->field($model, 'value')->dropDownList($settings[$model->name]['value']);
    } else {
        echo $form->field($model, 'value')->textInput(['maxlength' => true]);
    }
    Pjax::end();
    ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('goip', 'Create') : Yii::t('goip', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end();
    $this->registerJs(
        '$.pjax.submit({currentTarget: document.getElementById("settings-form"), preventDefault: function () {}}, "#setting_input");',
        View::POS_READY
    ); ?>
</div>
