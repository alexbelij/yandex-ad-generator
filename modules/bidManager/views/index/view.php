<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexCampaign */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-campaign-view">

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
            'account_id',
            'created_at',
            'updated_at',
            'title',
            'start_date',
            'end_date',
            'status',
            'state',
            'status_payment',
            'status_clarification',
            'stat_clicks',
            'stat_impressions',
            'currency',
            'funds_mode',
            'funds_sum',
            'funds_balance',
            'funds_shared_refund',
            'funds_shared_spend',
            'client_info',
            'daily_budget_amount',
            'daily_budget_mode',
            'strategy_1',
            'strategy_2',
        ],
    ]) ?>

</div>
