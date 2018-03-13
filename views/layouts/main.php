<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
\app\assets\CommonAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        body {
            padding-top: 65px;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    /** @var \app\models\Shop[] $shops */
    $shops = \app\models\Shop::find()->all();
    NavBar::begin([
        'brandLabel' => 'Yandex.Direct',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
        'innerContainerOptions' => ['class' => 'container-fluid']
    ]);

    $menuItems = [
        ['label' => 'Настройки', 'url' => ['#'], 'items' => [
            [
                'label' => 'Настройки',
                'url' => ['/settings']
            ],
            ['label' => 'Пользователи', 'url' => ['/users']],
            ['label' => 'Магазины', 'url' => ['/shops']],
            ['label' => 'Аккаунты', 'url' => ['/accounts']],
        ]]
    ];

    foreach ($shops as $shop) {
        $menuItem = [
            'label' => $shop->name,
            'url' => '#',
            'items' => [
                [
                    'label' => 'Настройка генератора',
                    'url' => ['/generator/general', 'shopId' => $shop->id]
                ],
                [
                    'label' => 'Ключевые слова',
                    'url' => ['/generator/keywords', 'shopId' => $shop->id]
                ],
                [
                    'label' => 'Шаблоны объявлений',
                    'url' => ['/generator/templates', 'shopId' => $shop->id]
                ],
                [
                    'label' => 'Шаблоны кампаний',
                    'url' => ['/campaign-templates', 'shopId' => $shop->id]
                ],
                [
                    'label' => 'Визитка',
                    'url' => ['/vcard', 'shopId' => $shop->id]
                ]
            ]
        ];

        if ($shop->external_strategy == \app\models\Shop::EXTERNAL_STRATEGY_YML) {
            $menuItem['items'][] = [
                'label' => 'Редактирование брендов',
                'url' => ['/external-brands', 'shopId' => $shop->id]
            ];
            $menuItem['items'][] = [
                'label' => 'Редактирование категорий',
                'url' => ['/external-category', 'shopId' => $shop->id]
            ];
            $menuItem['items'][] = [
                'label' => 'Редактирование товаров',
                'url' => ['/external-product', 'shopId' => $shop->id]
            ];
            $menuItem['items'][] = [
                'label' => 'Исключения',
                'url' => ['/word-exceptions', 'shopId' => $shop->id]
            ];
        }

        $menuItem['items'][] = [
            'label' => 'Black list',
            'url' => ['/black-list', 'shopId' => $shop->id]
        ];

        $menuItems[] = $menuItem;
    }

    $menuItems = array_merge($menuItems, [
        ['label' => 'Список кампаний', 'url' => ['/campaigns']],
        ['label' => 'Tasks', 'url' => ['/task-queue']],
        ['label' => 'Быстрые ссылки', 'url' => ['/sitelinks']],
        Yii::$app->user->isGuest ? (
        ['label' => 'Login', 'url' => ['/site/login']]
        ) : (
            '<li>'
            . Html::beginForm(['/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->login . ')',
                ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>'
        )
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container-fluid">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'homeLink' => false
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Yandex.Direct admin <?= date('Y') ?></p>

        <p class="pull-right">Developed by qimus.</p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
