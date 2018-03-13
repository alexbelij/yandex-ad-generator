<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexAdGroup */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Ad Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-ad-group-view">

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
            'account_id',
            'created_at',
            'updated_at',
            'name',
            'status',
            'type',
            'strategy_1',
            'strategy_2',
        ],
    ]) ?>

</div>
