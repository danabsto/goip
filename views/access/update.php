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
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $lines app\models\Line */
/* @var $form yii\widgets\ActiveForm */
/* @var $user User */

$this->title = Yii::t('goip', 'Setting permissions for') . '  ' . $user->username;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="access-update">
    <?= Alert::widget() ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin(['id' => 'access-form']); ?>
    <?= $form->field($user, 'linesArray')->checkboxList($lines, [
        'separator' => '<br>',
        'itemOptions' => [
            'class' => 'line'
        ]
    ])->label(Yii::t('goip', 'Lines') . ' <br><input type="checkbox" id="checkAll">' . Yii::t('goip', 'Check All') . '');
    ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('goip', 'Refresh'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
