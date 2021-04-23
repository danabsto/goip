<!--
Copyright 2021 Undefined.team

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
-->

<!DOCTYPE html>
<html lang="ru" data-is-logged-in="false">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>
        <?php echo Yii::t('goip', 'Convenient UI for your GoIP'); ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo Yii::t('goip', 'Only the most necessary functionality for receiving, sending messages and executing USSD commands.'); ?>"/>
    <link rel="stylesheet" href="/lp/css/lib/bootstrap.css">
    <link rel="stylesheet" href="/lp/css/base.css">
    <link rel="stylesheet" href="/lp/css/mainpage.css">
    <link rel="stylesheet" href="/lp/css/mainpage-new.css">
</head>
<body class="UL" data-version="v1">
<div class="main-wrap clearfix">
    <div id="header-new" class="nowhite dark always-fixed-menu">
        <div class="container">
            <div class="row flex-row-section header-new">
                <div class="flex-section">
                    <div class="logo-new">
                        <a href="/">
                            <img class="js-logo" src="/lp/img/logotype.png">
                            <img class="js-logo-mobile" style="display: none;" src="/lp/img/logotype.png">
                        </a>
                    </div>
                </div>
                <div class="flex-section">
                    <div class="menu-new hidden-xs">
                        <a class="menu-link" href="#main">
                            <?php echo Yii::t('goip', 'Main'); ?>
                        </a>
                        <a class="menu-link" href="#schemeofwork">
                            <?php echo Yii::t('goip', 'Scheme of work'); ?>
                        </a>
                        <a class="menu-link" href="#advantages">
                            <?php echo Yii::t('goip', 'Advantages'); ?>
                        </a>
                        <a class="menu-link" href="#faq">
                            <?php echo Yii::t('goip', 'FAQ'); ?>
                        </a>
                        <div class="signUp-new hidden-xs">
                            <a class="button button-trans-blue" href="/login">
                                <?php echo Yii::t('goip', 'Sign in'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="burger visible-xs-block">
                        <img class="js-burger-open" src="https://ukit.com/img/mainpage/newmainpage/locfree/header/menu-mobile-dark.svg">
                        <img class="js-burger-close burger__close" style="display: none;" src="https://ukit.com/img/mainpage/newmainpage/locfree/header/close.svg">
                    </div>
                </div>
            </div>
        </div>


        <div class="burger-menu js-burger-menu" style="display: none;" >
            <div>
                <div class="menu-mobile">
                    <a class="menu-link" href="#main">
                        <?php echo Yii::t('goip', 'Main'); ?>
                    </a>
                    <a class="menu-link" href="#schemeofwork">
                        <?php echo Yii::t('goip', 'Scheme of work'); ?>
                    </a>
                    <a class="menu-link" href="#advantages">
                        <?php echo Yii::t('goip', 'Advantages'); ?>
                    </a>
                    <a class="menu-link" href="#faq">
                        <?php echo Yii::t('goip', 'FAQ'); ?>
                    </a>
                    <div class="signUp-new">
                        <a class="button button-trans-blue" href="/login">
                            <?php echo Yii::t('goip', 'Sign in'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="whyus-container" id="main">
        <div class="container">
            <h1 class="ul-slogan"><?php echo Yii::t('goip', 'Convenient UI for your GoIP'); ?></h1>
            <h5 class="ul-about">
                <?php echo Yii::t('goip', 'Monitoring the balance and maintaining the SIM card in an active state makes the service more convenient.'); ?>
                <?php echo Yii::t('goip', 'Only the most necessary functionality for receiving, sending messages and executing USSD commands.'); ?><br>
            </h5>
            <img class="ul-whyus-image" src="/lp/img/window-mainpage.png">
        </div>
    </div>
    <div class="scheme-container" style="background: #fff;padding: 60px 0" id="schemeofwork">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="sectiontitle" style="text-align: center;margin-bottom: 50px;">
                        <?php echo Yii::t('goip', 'Scheme of work'); ?>
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" style="text-align: right;">
                    <img src="/lp/img/notebook.png" alt="" style="width: 100%;">
                </div>
                <div class="col-md-6">
                    <div style="margin-top: 25px;">
                        <h4 class="ul-text" style="font-size: 22px;"><?php echo Yii::t('goip', 'To work with our system you will need:'); ?></h4>
                        <ul style="padding-inline-start: 0px;">
                            <li class="ul-text">
                                <?php echo Yii::t('goip', 'Configure an external access to the GoIP'); ?>
                            </li>
                            <li class="ul-text">
                                <?php echo Yii::t('goip', 'Connect to your GoIP through our service'); ?>
                            </li>
                            <li class="ul-text">
                                <?php echo Yii::t('goip', 'Customize our service to your needs'); ?>
                            </li>
                        </ul>
                        <div class="centermobile">
                            <a href="/login" class="button-pref">
                                <?php echo Yii::t('goip', 'Get demo access'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="about-ukit-container" id="advantages">
        <div class="container">
            <div class="item-list">
                <div class="image-about">
                    <img class="ul-whyus-image" src="/lp/img/sim.png">
                </div>
                <div class="text-about">
                    <h3 class="ul-title"><?php echo Yii::t('goip', 'SIM card update'); ?></h3>
                    <h5 class="ul-text"><?php echo Yii::t('goip', 'Protection against blocking by the operator of the SIM card due to its non-use (no calls to other numbers).'); ?></h5>
                </div>
            </div>
            <div class="item-list">
                <div class="image-about right">
                    <img class="ul-whyus-image" src="/lp/img/monitoring.png">
                </div>
                <div class="text-about">
                    <h3 class="ul-title"><?php echo Yii::t('goip', 'Convenient monitoring'); ?></h3>
                    <h5 class="ul-text"><?php echo Yii::t('goip', 'The interface allows you to track all SIM-cards at once, to see their balance and to understand for what purposes the SIM-card is used.'); ?></h5>
                </div>
            </div>

            <div class="item-list">
                <div class="image-about">
                    <img class="ul-whyus-image" src="/lp/img/messages.png">
                </div>
                <div class="text-about">
                    <h3 class="ul-title"><?php echo Yii::t('goip', 'Fast message reception'); ?></h3>
                    <h5 class="ul-text"><?php echo Yii::t('goip', 'Our service checks for new messages once every 1 minute. This way you will be able to receive messages fairly quickly.'); ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="answers-container buttons-container" id="faq">
        <div class="content-block content-block--margin">
            <h3 class="sectiontitle" style="text-align: center;"><?php echo Yii::t('goip', 'FAQ'); ?></h3>
            <div class="block-faq clearfix" style="text-align: left;">
                <div class="block-faq-qa">
                    <div class="block-faq-qa__q"><?php echo Yii::t('goip', 'Is it safe?'); ?></div>

                    <div class="block-faq-qa__a"><?php echo Yii::t('goip', 'We use a secure data transfer protocol, all important data is stored and sent in an encrypted form.'); ?></div>
                </div>

                <div class="block-faq-qa">
                    <div class="block-faq-qa__q"><?php echo Yii::t('goip', 'Where balance information is stored?'); ?></div>

                    <div class="block-faq-qa__a"><?php echo Yii::t('goip', 'Balance information is stored on our servers. The system automatically makes a request to the operator and keeps the balance information up to date.'); ?></div>
                </div>

                <div class="block-faq-qa">
                    <div class="block-faq-qa__q"><?php echo Yii::t('goip', 'Will third parties be able to gain access to my data?'); ?></div>

                    <div class="block-faq-qa__a"><?php echo Yii::t('goip', 'No, we do not share nor sell our customer data and we protect it against leakage as much as possible.'); ?></div>
                </div>
                <div class="block-faq-qa">
                    <div class="block-faq-qa__q"><?php echo Yii::t('goip', 'I still have questions'); ?></div>

                    <div class="block-faq-qa__a"><?php echo Yii::t('goip', 'Write us an'); ?> <a href="mailto:support@goipapi.com">e-mail</a> <?php echo Yii::t('goip', 'and we will answer them!'); ?></div>
                </div>
            </div>
            <a href="/login" class="button-pref">
                <?php echo Yii::t('goip', 'Get start'); ?>
            </a>
        </div>
    </div>
    <div id="auth_primary_container" class="UL">
        <div id="auth_main_container"></div>
    </div>
</div>
<div id="footer" class="js-mobile-hideable footer-new">
    <div class="container">
        <div class="row">
            <div class="col-md-12" style="text-align: center;color: #777;">
                <div class="" style="margin-bottom: 5px;">
                    <?php echo Yii::t('goip', 'All rights reserved'); ?>
                </div>
                <div>
                    <a href="" style="color: #777;"><?php echo Yii::t('goip', 'Terms of Agreement'); ?></a> | <a href="" style="color: #777;"><?php echo Yii::t('goip', 'Terms of use'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        $(".menu-new > a").on("click", function (event) {
            event.preventDefault();
            var id  = $(this).attr('href'),
                top = $(id).offset().top-74;
            $('body,html').animate({scrollTop: top}, 1500);
        });
        $(".js-burger-open").on("click", function (event) {
            $(this).hide();
            $(".js-burger-close").show();
            $(".js-burger-menu").show();
        });
        $(".js-burger-close").on("click", function (event) {
            $(this).hide();
            $(".js-burger-open").show();
            $(".js-burger-menu").hide();
        });

        $(".menu-mobile > a").on("click", function (event) {
            $(this).hide();
            $(".js-burger-open").show();
            $(".js-burger-menu").hide();
        });
    });
</script>
</body>
</html>