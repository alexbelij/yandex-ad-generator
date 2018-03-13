<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\ExtrernalProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="external-product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'outer_id') ?>

    <?= $form->field($model, 'shop_id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'brand_id') ?>

    <?php // echo $form->field($model, 'category_id') ?>

    <?php // echo $form->field($model, 'is_available') ?>

    <?php // echo $form->field($model, 'picture') ?>

    <?php // echo $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'currency_id') ?>

    <?php // echo $form->field($model, 'old_price') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'file_import_id') ?>

    <?php // echo $form->field($model, 'model') ?>

    <?php // echo $form->field($model, 'original_title') ?>

    <?php // echo $form->field($model, 'type_prefix') ?>

    <?php // echo $form->field($model, 'is_manual') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
