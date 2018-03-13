<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\search\YandexBidSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="yandex-bid-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'campaign_id') ?>

    <?= $form->field($model, 'group_id') ?>

    <?= $form->field($model, 'keyword_id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'bid_serving_status') ?>

    <?php // echo $form->field($model, 'bid') ?>

    <?php // echo $form->field($model, 'context_bid') ?>

    <?php // echo $form->field($model, 'bid_min_search_price') ?>

    <?php // echo $form->field($model, 'bid_current_search_price') ?>

    <?php // echo $form->field($model, 'competitors_bids') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
