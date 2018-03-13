<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Vcard */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="vcard-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= Html::hiddenInput($model->formName() . '[shop_id]', $model->shop_id)?>

    <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'work_time')->textInput(['maxlength' => true])
        ->label('<span>Рабочее время <i class="glyphicon glyphicon-question-sign help-work-time"></i> </span>')
    ?>

    <div class="col-sm-2">
        <?= $form->field($model, 'phone_country_code')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($model, 'phone_city_code')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-6">
        <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($model, 'phone_extension')->textInput(['maxlength' => true]) ?>
    </div>

    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

    <div class="col-sm-3">
        <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($model, 'house')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($model, 'building')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($model, 'apartment')->textInput(['maxlength' => true]) ?>
    </div>

    <?= $form->field($model, 'extra_message')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ogrn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Отмена', \yii\helpers\Url::to(['/vcard', 'shopId' => $model->shop_id]), ['class' => 'btn btn-default'])?>
    </div>

    <?php ActiveForm::end(); ?>

    <div id="help-work-time-block">
        <p>Режим работы организации или режим обслуживания клиентов. Задается как строка, в которой указан диапазон дней недели, рабочих часов и минут.</p>
        <p>Дни недели обозначаются цифрами от 0 до 6, где 0 — понедельник, 6 — воскресенье.</p>
        <p>Минуты задают кратно 15: 0, 15, 30 или 45.</p>
        <p>Формат строки: “день_с;день_по;час_с;минуты_с;час_до;мин_до“.</p>
        <p>Например, строка “0;4;10;0;18;0“ задает такой режим:
        0;4 — с понедельника по пятницу;
        10;0 — с 10 часов 0 минут;
        18;0 — до 18 часов 0 минут.</p>
        <p>Режим может состоять из нескольких строк указанного формата, например: “0;4;10;0;18;0;5;6;11;0;16;0“. Здесь в дополнение к предыдущему примеру задан режим:
        5;6 — с субботы по воскресенье;
        11;0 — с 11 часов 0 минут;
        16;0 — до 16 часов 0 минут.</p>
        <p>Круглосуточный режим работы задается строкой “0;6;00;00;00;00“.</p>
        <p>Не более 255 символов.</p>
    </div>
</div>

<?php
$this->registerJs('
$(".help-work-time").popover({
    content: $("#help-work-time-block").html(),
    html: true
});

$("#help-work-time-block").remove();

$(".help-work-time").mouseover(function() {
    $(".help-work-time").popover("show");
});

$(".help-work-time").mouseout(function() {
    $(".help-work-time").popover("hide");
});
');