<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexBid */

$this->title = 'Create Yandex Bid';
$this->params['breadcrumbs'][] = ['label' => 'Yandex Bids', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-bid-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
