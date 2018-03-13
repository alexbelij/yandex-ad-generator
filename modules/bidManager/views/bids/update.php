<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexBid */

$this->title = 'Update Yandex Bid: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Bids', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="yandex-bid-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
