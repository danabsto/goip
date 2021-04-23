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

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $linesDataProvider yii\data\ActiveDataProvider */
/* @var $devices \app\models\Device[] */
/* @var $shared_devices \app\models\Device[] */

$this->title = Yii::t('goip', 'Dashboard');
?>
<div class="lines-index">
    <?=Html::a(Yii::t('goip', 'Create device'), ["devices/create"], ["class" => "btn btn-primary"]);?>
    <?php foreach ($devices as $device): ?>
        <div class="well well-sm" style="background-color: #d9edf7; border-color: #bce8f1; color: #31708f; margin-top: 20px;">
            <b><?=$device["title"];?></b>
            (<?=time()-strtotime($device["tm_activity"])>=60*60*24?Html::tag("span", \Yii::$app->formatter->asRelativeTime($device["tm_activity"]), ["style" => "color: red"]):\Yii::$app->formatter->asRelativeTime($device["tm_activity"]);?>)
            (<?= $device->user_id == Yii::$app->user->id ? Yii::t('goip', 'you are the owner') : Yii::t('goip', 'shared') ?>)
            <?= Html::a(Yii::t('goip', 'Device settings'), ['devices/settings', 'id' => $device['id']], ['style' => 'float: right']); ?>
        </div>
        <div class="row">
            <?php foreach ($device->getLines(Yii::$app->user->id)->all() as $line): ?>
            <?php if ($line->shouldBeDisplayed): ?>
                <?php $balance = ArrayHelper::getValue($line, "simcard.balance"); ?>
                <div class="col-md-3">
                    <div class="panel panel-default <?=$line["simcard_id"]?"panel-success":"panel-danger";?>">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <?= Html::a($line->fullTitle, $line->fullTitleUrl); ?>&nbsp;
                                <?php if($line["simcard"]): ?>
                                    <span data-line-id="<?=$line["id"];?>" class="balance pull-right" style="color: <?=$balance>=100?"green":"red";?>"><?=\Yii::$app->formatter->asCurrency($balance, 'RUB');?></span>
                                <?php endif; ?>
                            </h3>
                        </div>
                        <div class="panel-body" style="height: 104px">
                            <?php if($line["simcard"]): ?>
                            <table class="balances">
                                <tr>
                                    <td><?php echo Yii::t('goip', 'Month'); ?></td>
                                    <td><?php echo Yii::t('goip', 'Week'); ?></td>
                                    <td><?php echo Yii::t('goip', 'Day'); ?></td>
                                </tr>
                                <tr>
                                    <td><?=\Yii::$app->formatter->asCurrency($balance - ArrayHelper::getValue($line, ["simcard.monthBalance.balance"]), "RUB");?></td>
                                    <td><?=\Yii::$app->formatter->asCurrency($balance - ArrayHelper::getValue($line, ["simcard.weekBalance.balance"]), "RUB");?></td>
                                    <td><?=\Yii::$app->formatter->asCurrency($balance - ArrayHelper::getValue($line, ["simcard.yesterdayBalance.balance"]), "RUB");?></td>
                                </tr>
                            </table>
                                <p class="lastinfo"><?php echo Yii::t('goip', 'SMS was'); ?> <?= \Yii::$app->formatter->asRelativeTime(ArrayHelper::getValue($line, ['lastMessageTm', 'tm_create'])); ?></p>
                                <p class="lastinfo"><?php echo Yii::t('goip', 'Call was'); ?> <?= \Yii::$app->formatter->asRelativeTime(ArrayHelper::getValue($line, ['lastCallTm', 'tm'])); ?></p>
                            <?php else: ?>

                            <?php endif; ?>
                        </div>
                        <div class="panel-footer">
                            <?php $simcard_phone = !empty(ArrayHelper::getValue($line, 'simcard'))
                                ? !empty(ArrayHelper::getValue($line, 'simcard.phone'))
                                    ? ArrayHelper::getValue($line, "simcard.phone")
                                    : Yii::t('goip', 'Unknown number')
                                : Yii::t('goip', 'No simcard'); ?>
                            <span style="font-size: 11px"><?= $device->user_id == Yii::$app->user->id
                                    ? Html::a($simcard_phone, ['lines/update', 'id' => $line->id])
                                    : $simcard_phone ?></span>
                            <span class="pull-right">
                                <?php if ($line->device->getAttribute('user_id') == Yii::$app->user->id): ?>
                                <?= Html::a('<i class="glyphicon glyphicon-cog"></i>', Url::toRoute(['lines/update', 'id' => ArrayHelper::getValue($line, 'id')]), ['class' => 'btn btn-xs']) ?>
                                <?php endif; ?>
                                <?php if($line["simcard"]): ?>
                                <button data-toggle="modal" data-target="#callsModal" data-id="<?=$line["id"];?>" data-phone="<?=ArrayHelper::getValue($line, "simcard.phone");?>" class="btn btn-xs btn-primary" title="<?php echo Yii::t('goip', 'Call'); ?>"><i class="glyphicon glyphicon-earphone"></i></button>
                                <button data-toggle="modal" data-target="#smsModal" data-id="<?=$line["id"];?>" data-phone="<?=ArrayHelper::getValue($line, "simcard.phone");?>" class="btn btn-xs btn-warning" title="<?php echo Yii::t('goip', 'SMS'); ?>"><i class=" glyphicon glyphicon-comment"></i></button>
                                <button data-toggle="modal" data-target="#ussdModal" data-id="<?=$line["id"];?>" data-phone="<?=ArrayHelper::getValue($line, "simcard.phone");?>" class="btn btn-xs btn-info" title="<?php echo Yii::t('goip', 'USSD'); ?>"><i class="glyphicon glyphicon-asterisk"></i></button>
                                <button data-toggle="modal" data-target="#forwardModal" data-id="<?=$line["id"];?>" data-forward="<?=preg_replace("/(\d)(\d{10})/", "$2", $line["forward"]);?>" data-phone="<?=ArrayHelper::getValue($line, "simcard.phone");?>" class="btn btn-xs <?=$line["forward"]?"btn-success":"btn-info";?>" title="<?php echo Yii::t('goip', 'Setup forward'); ?>"><i class="glyphicon glyphicon-forward"></i></button>
                            <?php else: ?>
                                <button class="btn btn-xs btn-success" title="<?php echo Yii::t('goip', 'Reload module'); ?>"  data-id="<?=$line["id"];?>" data-toggle="reload"><i class="glyphicon glyphicon-refresh"></i></button>
                            <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Форма отправки USSD запроса -->
<div class="modal fade" id="ussdModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <input type="hidden" id="ussdLineId">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="balanceModalLabel"><?php echo Yii::t('goip', 'Send USSD'); ?></h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="ussd" class="control-label"><?php echo Yii::t('goip', 'Command'); ?>:</label>
                        <input type="text" class="form-control" id="ussd">
                    </div>
                </form>
                <div class="alert alert-info"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('goip', 'Close'); ?></button>
                <button type="button" class="btn btn-primary"><?php echo Yii::t('goip', 'Send'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Форма отправки звонка -->
<div class="modal fade" id="callsModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <input type="hidden" id="callsLineId">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="balanceModalLabel"><?php echo Yii::t('goip', 'Send a call'); ?></h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="phone" class="control-label"><?php echo Yii::t('goip', 'Number'); ?>:</label>
                        <?= MaskedInput::widget([
                            'name' => 'phone',
                            'mask' => '+7 (999) 999-99-99',
                        ]); ?>
                        </div>
                    <div class="form-group">
                        <label for="photo" class="control-label"><?php echo Yii::t('goip', 'Duration'); ?>:</label>
                        <input type="text" class="form-control" id="duration" value="10">
                    </div>
                </form>
                <div class="alert alert-info"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('goip', 'Close'); ?></button>
                <button type="button" class="btn btn-primary"><?php echo Yii::t('goip', 'Send'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Форма отправки звонка -->
<div class="modal fade" id="forwardModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <input type="hidden" id="forwardLineId">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="forwardModalLabel"><?php echo Yii::t('goip', 'Setup forward'); ?></h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="phone" class="control-label"><?php echo Yii::t('goip', 'Number'); ?>:</label>
                        <?= MaskedInput::widget([
                            'name' => 'phone',
                            'mask' => '+7 (999) 999-99-99',
                        ]); ?>
                    </div>
                </form>
                <div class="alert alert-info"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" style="display: none"><?php echo Yii::t('goip', 'Disable'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('goip', 'Close'); ?></button>
                <button type="button" class="btn btn-primary"><?php echo Yii::t('goip', 'Save'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Форма отправки смс -->
<div class="modal fade" id="smsModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <input type="hidden" id="smsLineId">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="balanceModalLabel"><?php echo Yii::t('goip', 'Send SMS'); ?></h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="phone" class="control-label"><?php echo Yii::t('goip', 'Number'); ?>:</label>
                        <?=Html::textInput("phone", "", ["class" => "form-control"]);?>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="control-label"><?php echo Yii::t('goip', 'Text'); ?>:</label>
                        <textarea class="form-control" id="message"></textarea>
                    </div>
                </form>
                <div class="alert alert-info"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('goip', 'Close'); ?></button>
                <button type="button" class="btn btn-primary"><?php echo Yii::t('goip', 'Send'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php if(false): ?><script><?php endif; ?>

    <?php ob_start(); ?>


    $('[data-toggle="reload"]').on("click", function() {
        var lineID = button.data('id');
        $.getJSON('<?=Url::toRoute(["site/reload"]);?>', {
            id: lineID
        }, function(response) {
            location.reload();
        });
    });

    $('#ussdModal').on('show.bs.modal', function (event) {
        $('#ussdModal').find('.alert').hide();
        var button = $(event.relatedTarget);

        var lineID = button.data('id');
        var phone = button.data('phone');

        var modal = $(this);
        modal.find('.modal-title').text('<?php echo Yii::t('goip', 'Send USSD'); ?> ' + phone);
        modal.find('#ussdLineId').val(lineID);
    });

    $('#ussdModal').find('.btn-primary').click(function() {
        var self = $(this);
        self.attr('disabled', 'disabled');
        var ussd = $('#ussdModal').find('#ussd').val();
        var lineID = $('#ussdModal').find('#ussdLineId').val();
        $.post('<?=Url::toRoute(["site/send-ussd"]);?>', {
            id: lineID,
            ussd: ussd
        }, function(response) {
            self.removeAttr('disabled');
            if(response.success === 1) {
                $('#ussdModal').find('.alert').html(response.result).show();
            } else {
                alert(response.error);
            }
        }, 'json');
    });

    $('#smsModal').on('show.bs.modal', function (event) {
        $('#smsModal').find('.alert').hide();
        var button = $(event.relatedTarget);

        var lineID = button.data('id');
        var phone = button.data('phone');

        var modal = $(this);
        modal.find('.modal-title').text('<?php echo Yii::t('goip', 'Send SMS'); ?>');
        modal.find('#smsLineId').val(lineID);
    });

    $('#smsModal').find('.btn-primary').click(function() {
        $.ajaxSetup({
            async: true
        });
        var self = $(this);
        self.attr('disabled', 'disabled');
        var phone = $('#smsModal').find('[name=phone]').val();
        var message = $('#smsModal').find('#message').val();
        var lineID = $('#smsModal').find('#smsLineId').val();

        $.post('<?=Url::toRoute(["site/send-sms"]);?>', {
            id: lineID,
            phone: phone,
            message: message
        }, function(response) {
            self.removeAttr('disabled');
            if(response.success === 1) {
                $('#smsModal').find('.alert').html(response.result).show();
            } else {
                alert(response.error);
            }
        }, 'json');
    });

    $('#callsModal').on('show.bs.modal', function (event) {
        $('#callsModal').find('.alert').hide();
        var button = $(event.relatedTarget);

        var lineID = button.data('id');
        var phone = button.data('phone');

        var modal = $(this);
        modal.find('.modal-title').text('<?php echo Yii::t('goip', 'Send a call'); ?>');
        modal.find('#callsLineId').val(lineID);
    });

    $('#callsModal').find('.btn-primary').click(function() {
        $.ajaxSetup({
            async: true
        });
        var self = $(this);
        self.attr('disabled', 'disabled');
        var phone = $('#callsModal').find('[name=phone]').val();
        var duration = $('#callsModal').find('#duration').val();
        var lineID = $('#callsModal').find('#callsLineId').val();

        $.post('<?=Url::toRoute(["site/send-call"]);?>', {
            id: lineID,
            phone: phone,
            duration: duration
        }, function(response) {
            if(response.success === 1) {
                //$('#callsModal').find('.alert').html(response.result).show();
                var intervalCall = setInterval(function() {
                    $.getJSON('<?=Url::toRoute(["site/check-call"]);?>', {
                        id: lineID
                    }, function(response) {
                        $('#callsModal').find('.alert').html(response.result).show();
                        if(response.result === "IDLE") {
                            clearInterval(intervalCall);
                            self.removeAttr('disabled');
                        }
                    });
                }, 1000);
            } else {
                alert(response.error);
            }
        }, 'json');
    });

    $('#forwardModal').on('show.bs.modal', function (event) {
        $('#forwardModal').find('.alert').hide();
        var button = $(event.relatedTarget);

        var lineID = button.data('id');
        var phone = button.data('phone');
        var forward = button.data('forward');
        if(forward != '') $('#forwardModal').find('.btn-danger').show();
        else $('#forwardModal').find('.btn-danger').hide();
        $('#forwardModal').find('[name=phone]').val(forward);

        var modal = $(this);
        modal.find('.modal-title').text('<?php echo Yii::t('goip', 'Set forward'); ?>');
        modal.find('#forwardLineId').val(lineID);
    });


    $('#forwardModal').find('.btn-danger').click(function() {
        $.ajaxSetup({
            async: true
        });
        var self = $(this);
        self.attr('disabled', 'disabled');
        var phone = $('#forwardModal').find('[name=phone]').val();
        var lineID = $('#forwardModal').find('#forwardLineId').val();

        $.post('<?=Url::toRoute(["site/set-forward"]);?>', {
            id: lineID,
            phone: phone,
            s: 0
        }, function(response) {
            if(response.success === 1) {
                $('#forwardModal').find('.alert').html('DONE').show();
                self.removeAttr('disabled');
                location.reload();
            } else {
                alert(response.error);
            }
        }, 'json');
    });

    $('#forwardModal').find('.btn-primary').click(function() {
        $.ajaxSetup({
            async: true
        });
        var self = $(this);
        self.attr('disabled', 'disabled');
        var phone = $('#forwardModal').find('[name=phone]').val();
        var lineID = $('#forwardModal').find('#forwardLineId').val();

        $.post('<?=Url::toRoute(["site/set-forward"]);?>', {
            id: lineID,
            phone: phone,
            s: 1
        }, function(response) {
            if(response.success === 1) {
                $('#forwardModal').find('.alert').html('DONE').show();
                self.removeAttr('disabled');
                location.reload();
            } else {
                alert(response.error);
            }
        }, 'json');
    });

    $('.balance').on('click', function() {
        var self = $(this);
        self.html($('<img />').attr('src', '/img/spinner.gif')).removeClass('balance');
        var lineID = self.data("line-id");
        $.getJSON('<?=Url::toRoute(["site/check-balance"]);?>', { id: lineID }, function(response) {
            self.html(response.balance).css('color', response.color).addClass('balance');
        });
    });

    <?php $js = ob_get_contents(); ob_end_clean(); $this->registerJs($js); ?>
