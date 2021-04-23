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
/* @var array $linesDD */
/* @var $line Line */
/* @var $message Message */
/* @var array $devices \app\models\Device */

/* @var int $messages_per_page */

use app\models\Line;
use app\models\Message;
use yii\helpers\Html;

$this->title = Yii::t('goip', 'Messages');
$number = "";

if ($line) {
    $this->title = join(" / ", [$this->title, $line->device->title, $line->title]);
    $number = sprintf("Порт %s.", $line->number);
    echo '<span name="to-reload-page" data-interval="2"></span>';
}
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="messages-index">
    <?= $this->render('_list', [
        'devices' => $devices,
        'dataProvider' => $dataProvider,
        'number' => $number,
        'messages_per_page' => $messages_per_page,
        'paginationEnabled' => false,
    ]); ?>
    <div class="row">
        <?= Html::a(Yii::t('goip', 'Message archive'),
            ['archive', 'id' => $line->simcard_id ?? null],
            ['class' => 'btn btn-primary']) ?>
    </div>
</div>
