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

/* @var $this View */
/* @var $content string */

use app\assets\AppAsset;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'goipapi.com',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => Yii::t('goip', 'Dashboard'), 'url' => ['/site/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => Yii::t('goip', 'Messages'), 'url' => ['/messages/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => Yii::t('goip', 'Users'), 'url' => ['/user/admin/'], 'active' => substr_count(Url::current(), '/user/admin'), 'visible' => !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin],
            ['label' => Yii::t('goip', 'Settings'), 'url' => ['/settings/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => 'Sign In', 'url' => ['/user/security/login'], 'visible' => Yii::$app->user->isGuest],
            ['label' => 'Sign Up', 'url' => ['/user/register'], 'visible' => Yii::$app->user->isGuest],
            ['label' => 'Logout (' . (!Yii::$app->user->isGuest ? Yii::$app->user->identity->username : '') . ')',
                'url' => ['/user/security/logout'],
                'linkOptions' => ['data-method' => 'post'], 'visible' => !Yii::$app->user->isGuest],
        ]
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= date('Y') ?></p>
        <p class="pull-right"><?= Html::a(Yii::t('goip', 'Feedback'), ['site/contacts']); ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
