<?php

/* @var $this yii\web\View */

$this->title = 'Главная';
?>
<div class="site-index">

    <h1>Welcome</h1>
    <h2><?=\yii\bootstrap\Html::a('Управление ставками', \yii\helpers\Url::to(['/bid-manager']))?></h2>
    <h2><?=\yii\bootstrap\Html::a('Загрузка фидов', \yii\helpers\Url::to(['/feed']))?></h2>
    <h3><?=\yii\bootstrap\Html::a('Редиректы для быстрых ссылок', \yii\helpers\Url::to(['/feed/redirects']))?></h3>
</div>
