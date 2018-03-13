<?php

use app\assets\AppAsset;
use yii\bootstrap\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var string $content */

AppAsset::register($this);
\app\assets\CommonAsset::register($this);

$this->beginPage();
?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <? $this->head() ?>

    </head>

    <body>
    <? $this->beginBody() ?>
        <div class="wrap">

            <?php
            NavBar::begin([
                'brandLabel' => 'Загрузка фидов',
                'brandUrl' => \yii\helpers\Url::to(['/feed']),
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
                'innerContainerOptions' => ['class' => 'container-fluid']
            ]);

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-left'],
                'items' => [
                    [
                        'label' => 'Фиды',
                        'url' => \yii\helpers\Url::to(['/feed'])
                    ],
                ],
            ]);

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    [
                        'label' => 'Вернутся в генератор',
                        'url' => \yii\helpers\Url::to(['/']),
                        'options' => [
                            'style' => 'float: right;'
                        ]
                    ]
                ],
            ]);

            NavBar::end();

            ?>

            <div class="container-fluid" style="margin-top: 45px;">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    'homeLink' => false
                ]) ?>
                <?= $content ?>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; Модуль управления ставками <?= date('Y') ?></p>

                <p class="pull-right">Developed by qimus.</p>
            </div>
        </footer>

    </body>
    <? $this->endBody() ?>
    </html>
<? $this->endPage() ?>