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

use app\models\ShareCondition;
use app\models\Simcard;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $device app\models\Device */

?>

    <h1><?= Html::encode($this->title) ?></h1>

<?= Html::a(Yii::t('goip', 'Add share'), ["share/create", 'id' => $device['id']], ["class" => "btn btn-primary"]); ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['label' => 'Simcards', 'value' => function ($model) {
            $simcards = '';
            foreach ($model->simcards as $simcard)
                $simcards .= (empty($simcards) ? '' : ', ') . Simcard::getSimcardTitle($simcard);
            return $simcards;
        }],
        ['attribute' => 'user.email', 'label' => 'Доступно для'],
        ['attribute' => 'filters', 'format' => 'raw', 'value' => function ($model) {
            $filters = json_decode($model->filters, true);
            if (!isset($filters['comparison_condition'])) return null;
            $result = $model->getAttributeLabel($filters['comparison_condition']) . ":<br>";
            $shareCondition = new ShareCondition;
            if (!isset($filters['conditions'])) return null;
            foreach ($filters['conditions'] as $condition)
                $result .= '"' . $shareCondition->getAttributeLabel($condition['field']) . '"' . ' '
                    . $shareCondition->getAttributeLabel($condition['condition']) . ' '
                    . '"' . $condition['text'] . '"' . '<br>';
            return $result;
        }],
        'tm_updated:datetime',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{delete}'
        ],
    ],
]); ?>