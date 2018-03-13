<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ExternalBrand */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="external-brand-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'original_title')->textInput(['disabled' => true])?>

    <?= $form->field($model, 'is_deleted')->checkbox()?>

    <?=$form->field($model, 'is_manual')->checkbox()?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Отмена', \yii\helpers\Url::to(['/external-brands', 'shopId' => $model->shop_id]), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
