<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ExternalProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="external-product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'original_title')->textInput(['disabled' => true])?>

    <?= $form->field($model, 'brand_id')->widget(\kartik\select2\Select2::className(), [
        'data' => $model->getBrandsList(),
        'language' => 'ru',
        'options' => ['placeholder' => 'Выберите бренд ...'],
    ]) ?>

    <?= $form->field($model, 'category_id')->widget(\kartik\select2\Select2::className(), [
        'data' => $model->getCategoriesList(),
        'language' => 'ru',
        'options' => ['placeholder' => 'Выберете категорию ...'],
    ]) ?>

    <?= $form->field($model, 'is_available')->checkbox() ?>

    <?= $form->field($model, 'picture')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'type_prefix')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_manual')->checkbox() ?>

    <?= $form->field($model, 'is_generate_ad')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Отмена',\yii\helpers\Url::to(['/external-product', 'shopId' => $model->shop_id]), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
