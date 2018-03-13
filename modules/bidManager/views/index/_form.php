<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexCampaign */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="yandex-campaign-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_date')->textInput() ?>

    <?= $form->field($model, 'end_date')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_payment')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_clarification')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'stat_clicks')->textInput() ?>

    <?= $form->field($model, 'stat_impressions')->textInput() ?>

    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'funds_mode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'funds_sum')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'funds_balance')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'funds_shared_refund')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'funds_shared_spend')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'client_info')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'daily_budget_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'daily_budget_mode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'strategy_1')->textInput() ?>

    <?= $form->field($model, 'strategy_2')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
