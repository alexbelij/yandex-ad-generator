<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexBid */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="yandex-bid-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'campaign_id')->textInput() ?>

    <?= $form->field($model, 'group_id')->textInput() ?>

    <?= $form->field($model, 'keyword_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'bid_serving_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'context_bid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bid_min_search_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bid_current_search_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'competitors_bids')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
