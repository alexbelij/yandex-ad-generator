<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\QuickRedirect */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="quick-redirect-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'source')
        ->textInput(['maxlength' => true])
        ->hint(
        'Нужно ввести часть урла например, если ввести dostavka то получившийся урл будет иметь вид: http://generate-me.ru/q/dostavka',
        ['class' => 'tt', 'style' => 'font-style: italic;']
    ) ?>

    <?= $form->field($model, 'target')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
