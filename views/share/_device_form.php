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

use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $simcards app\models\Simcard[] */
/* @var $share app\models\ShareMessages */
/* @var $shareConditions app\models\ShareCondition[] */
/* @var $settings array */

$js = '
function postRender() {
    jQuery(".device_share_form_wrapper .panel-title-address").each(function(index) {
        jQuery(this).html("' . Yii::t("goip", "condition_condition") . ' " + (index + 1))
    });
    if (jQuery(".device_share_form_wrapper .panel-title-address").length > 1) {
        jQuery(".field-sharemessages-comparison_condition").show();
    } else {
        jQuery(".field-sharemessages-comparison_condition").hide();
    }
}

jQuery(".device_share_form_wrapper").on("afterInsert", function(e, item) {
    postRender()
});

jQuery(".device_share_form_wrapper").on("afterDelete", function(e) {
    postRender()
});

jQuery(".device_share_form_wrapper").ready(function(e) {
    postRender()
});';

$this->registerJs($js);

?>

<div class="settings-form">
    <?php $form = ActiveForm::begin(['id' => 'device-share-form']); ?>
    <?= $form->field($share, 'simcard_ids')->widget(Select2::class, [
        'data' => $simcards, 'options' => ['multiple' => true]
    ]);
    ?>
    <?= $form->field($share, 'share_to_email')->textInput(); ?>
    <h2><?= Yii::t('goip', 'messages_filter') ?></h2>
    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'device_share_form_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'limit' => 4, // the maximum times, an element can be cloned (default 999)
        'min' => 0, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $shareConditions[0],
        'formId' => 'device-share-form',
        'formFields' => [
            'field',
            'condition',
            'text',
        ],
    ]); ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-envelope"></i><?= Yii::t('goip', 'сomparison_conditions') ?>
            <button type="button" class="pull-right add-item btn btn-success btn-xs">
                <i class="fa fa-plus"></i>
                <?= Yii::t('goip', 'add_condition') ?>
            </button>
            <div class="clearfix"></div>
        </div>
        <div class="panel-body container-items">
            <?= $form->field($share, 'comparison_condition')->dropDownList([
                'or' => $share->getAttributeLabel('or'),
                'and' => $share->getAttributeLabel('and'),
            ]); ?>
            <?php foreach ($shareConditions as $index => $shareCondition): ?>
                <div class="item panel panel-default">
                    <div class="panel-heading">
                        <span class="panel-title-address"><?= Yii::t('goip', 'condition_condition') . " " . ($index + 1) ?></span>
                        <button type="button" class="pull-right remove-item btn btn-danger btn-xs"><i
                                    class="fa fa-minus"></i></button>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <?= $form->field($shareCondition, "[$index]field")->dropDownList([
                                    'address' => $shareCondition->getAttributeLabel('from'),
                                    'text' => $shareCondition->getAttributeLabel('message'),
                                ]) ?>
                            </div>
                            <div class="col-sm-4">
                                <?= $form->field($shareCondition, "[$index]condition")->dropDownList([
                                    'matches' => $shareCondition->getAttributeLabel('matches'),
                                    'doesntMatch' => $shareCondition->getAttributeLabel('doesntMatch'),
                                    'contains' => $shareCondition->getAttributeLabel('contains'),
                                    'doesntContain' => $shareCondition->getAttributeLabel('doesntContain'),
                                ]) ?>
                            </div>
                            <!-- TODO: fix bug with only first condition text validations works -->
                            <div class="col-sm-4">
                                <?= $form->field($shareCondition, "[$index]text")->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php DynamicFormWidget::end(); ?>
    <div class="form-group">
        <?= Html::submitButton($share->isNewRecord ? Yii::t('goip', 'Create') : Yii::t('goip', 'Update'), ['class' => $share->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
