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
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var array $devices \app\models\Device */
/* @var int $messages_per_page */
/* @var $paginationEnabled bool */

/* @var int $number */

use app\components\Alert;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?= Alert::widget() ?>
<div class="row">
    <div class="alert alert-info">
        <?= $number; ?>
        <?= Yii::t('goip', 'Last update'); ?>
        <?php foreach ($devices as $device): ?>
            <b><?= $device->title ?></b>
            <span>(<?= Html::tag('span', Yii::$app->formatter->asRelativeTime($device->tm_activity),
                    time() - strtotime($device->tm_activity) >= 60 * 60 * 24 ? ['style' => 'color: red'] : []) ?>)</span>,
        <?php endforeach ?>
    </div>
    <?php $page = Yii::$app->request->get('page') ?? 1;
    $dataProvider->query = $dataProvider->query->limit($messages_per_page)->offset($messages_per_page * ($page - 1));
    $messages = $dataProvider->getModels();
    $messages_count = count($messages);
    foreach ($messages as $message): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span><?= Yii::t('goip', 'From') ?></span>
                            <b><?= $message['address'] ?></b>
                            <span><?= Yii::t('goip', 'received') ?></span>
                            <b><?= Yii::$app->formatter->asDate($message['tm'], 'd MMMM') ?></b>
                            <span>в</span>
                            <b><?= Yii::$app->formatter->asTime($message['tm'], "H:mm") ?></b>
                            <span>(<?= Yii::$app->formatter->asRelativeTime($message['tm']) ?>)</span>
                            <span class="pull-right">
                                <?= $message['device_title'] ?> - <?= $message['line_title'] ?>,
                                <?= preg_replace("/(\d)(\d{3})(\d{3})(\d{2})(\d{2})/",
                                    "+$1 ($2) $3-$4-$5", $message['simcard_phone']) ?>
                                <?php if ($message['user_id'] == Yii::$app->user->id): ?>
                                    <a href="/messages/delete?id=<?= $message['id'] ?>"
                                       title="<?= Yii::t('goip', 'Delete') ?>"
                                       aria-label="<?= Yii::t('goip', 'Delete') ?>" data-pjax="0"
                                       data-confirm="<?= Yii::t('goip', 'Delete message?') ?>"
                                       data-method="post"><span class="glyphicon glyphicon-trash"></span></a>
                                <?php endif ?>
                            </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <p><?= $message['text']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach;
    if ($paginationEnabled): ?>
        <ul class="pagination">
            <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                <?php if ($page == 1): ?>
                    <span class="page-link">Previous</span>
                <?php else: ?>
                    <a class="page-link"
                       href="<?= Url::toRoute([Yii::$app->request->getBaseUrl(),
                           'id' => Yii::$app->request->get('id'),
                           'page' => $page - 1]) ?>">Previous</a>
                <?php endif; ?>
            </li>
            <li class="page-item <?php if ($messages_count < $messages_per_page) echo 'disabled'; ?>">
                <?php if ($messages_count < $messages_per_page): ?>
                    <span class="page-link">Next</span>
                <?php else: ?>
                    <a class="page-link" href="<?= Url::toRoute([Yii::$app->request->getBaseUrl(),
                        'id' => Yii::$app->request->get('id'),
                        'page' => $page + 1]) ?>">Next</a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</div>