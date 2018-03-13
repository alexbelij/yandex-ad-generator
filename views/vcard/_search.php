<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\VcardSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="vcard-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'shop_id') ?>

    <?= $form->field($model, 'country') ?>

    <?= $form->field($model, 'city') ?>

    <?= $form->field($model, 'company_name') ?>

    <?php // echo $form->field($model, 'work_time') ?>

    <?php // echo $form->field($model, 'phone_country_code') ?>

    <?php // echo $form->field($model, 'phone_city_code') ?>

    <?php // echo $form->field($model, 'phone_number') ?>

    <?php // echo $form->field($model, 'phone_extension') ?>

    <?php // echo $form->field($model, 'street') ?>

    <?php // echo $form->field($model, 'house') ?>

    <?php // echo $form->field($model, 'building') ?>

    <?php // echo $form->field($model, 'apartment') ?>

    <?php // echo $form->field($model, 'extra_message') ?>

    <?php // echo $form->field($model, 'contact_email') ?>

    <?php // echo $form->field($model, 'ogrn') ?>

    <?php // echo $form->field($model, 'contact_person') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
