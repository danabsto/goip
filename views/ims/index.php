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
use app\models\IMS;
use app\models\IMSForm;
use yii\helpers\Html;
use yii\grid\GridView;
use \yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\components\Alert;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $imsConnections yii\data\ActiveDataProvider */
/* @var $imsCode \app\models\IMSForm */

$bot_name = Yii::$app->params['ims']['telegram']['bot_name'];
$bot_url = "https://t.me/{$bot_name}";

$this->title = Yii::t('goip', 'Подключения IMS');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('/settings/_menu'); ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <div class="ims-index">
                    <?= Alert::widget() ?>

                    <h1><?= Html::encode($this->title) ?></h1>

                    <p>Для добавления нового контакта в одной из систем обмена мгновенными сообщениями выполните
                        следующие инструкции.</p>
                    <p>Добавьте в список контактов идентификатор: <b><a href="<?= $bot_url ?>">@<?= $bot_name ?></a></b>,
                        перейдя по адресу: <a href="<?= $bot_url ?>"><?= $bot_url ?></a></p>
                    <p>Отправьте сообщение: <b>/login</b></p>

                    <?php $form_a = ActiveForm::begin(); ?>
                    <p>
                    <div class="fLine">
                        Введите код, полученный в ответном
                        сообщении: <?= $form_a->field($imsCode, "validation_code")->textInput(['style' => 'width:100px'])->label(false); ?>
                        <input class="btn btn-primary" style="display: inline;"
                               value="<?php echo Yii::t('app', 'Connect'); ?>" type="submit"></div>
                    </p>
                    <?php ActiveForm::end(); ?>

                    <?= GridView::widget([
                        'dataProvider' => $imsConnections,
                        'columns' => [
                            'service',
                            'user_id',
                            'name',
                            'active:boolean',
                            'tm_created:datetime',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{delete}',
                            ],
                        ],

                    ]); ?>

                </div>
            </div>
        </div>
    </div>
</div>
