<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\Feed */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feed-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'domain')->textInput(['maxlength' => true]) ?>

    <?=$form->field($model, 'subid')
        ->textInput()
        ->hint(
            'subid=[category]&subid1=[brand]&subid2=[model]&subid3=[price]',
            ['class' => 'tt', 'style' => 'font-style: italic;']
        )
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
