<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ExternalProduct */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'External Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-product-view">

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
            'outer_id',
            'shop_id',
            'title',
            'brand_id',
            'category_id',
            'is_available',
            'picture',
            'url:url',
            'currency_id',
            'old_price',
            'price',
            'created_at',
            'updated_at',
            'file_import_id',
            'model',
            'original_title',
            'type_prefix',
            'is_manual',
        ],
    ]) ?>

</div>
