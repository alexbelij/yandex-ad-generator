<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\FileImportLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="file-import-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'file_import_id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'operation') ?>

    <?= $form->field($model, 'old_value') ?>

    <?php // echo $form->field($model, 'new_value') ?>

    <?php // echo $form->field($model, 'entity_type') ?>

    <?php // echo $form->field($model, 'entity_id') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
