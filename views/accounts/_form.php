<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\YandexAccount;

/* @var $this yii\web\View */
/* @var $model YandexAccount */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?=$form->field($model, 'yandexApplicationId')?>
    <?=$form->field($model, 'yandexSecret')?>

    <?= Html::hiddenInput($model->formName() . '[account_type]', $model::ACCOUNT_TYPE_YANDEX)?>

    <?= $form->field($model, 'token')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
