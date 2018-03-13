<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\search\YandexCampaignSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="yandex-campaign-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'account_id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <?= $form->field($model, 'title') ?>

    <?php // echo $form->field($model, 'start_date') ?>

    <?php // echo $form->field($model, 'end_date') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'state') ?>

    <?php // echo $form->field($model, 'status_payment') ?>

    <?php // echo $form->field($model, 'status_clarification') ?>

    <?php // echo $form->field($model, 'stat_clicks') ?>

    <?php // echo $form->field($model, 'stat_impressions') ?>

    <?php // echo $form->field($model, 'currency') ?>

    <?php // echo $form->field($model, 'funds_mode') ?>

    <?php // echo $form->field($model, 'funds_sum') ?>

    <?php // echo $form->field($model, 'funds_balance') ?>

    <?php // echo $form->field($model, 'funds_shared_refund') ?>

    <?php // echo $form->field($model, 'funds_shared_spend') ?>

    <?php // echo $form->field($model, 'client_info') ?>

    <?php // echo $form->field($model, 'daily_budget_amount') ?>

    <?php // echo $form->field($model, 'daily_budget_mode') ?>

    <?php // echo $form->field($model, 'strategy_1') ?>

    <?php // echo $form->field($model, 'strategy_2') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
