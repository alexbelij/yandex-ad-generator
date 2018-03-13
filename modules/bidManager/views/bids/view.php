<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexBid */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Bids', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-bid-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'campaign_id',
            'group_id',
            'keyword_id',
            'created_at',
            'updated_at',
            'bid_serving_status',
            'bid',
            'context_bid',
            'bid_min_search_price',
            'bid_current_search_price',
            'competitors_bids:ntext',
        ],
    ]) ?>

</div>
