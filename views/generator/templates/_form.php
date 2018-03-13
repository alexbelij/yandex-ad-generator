<?php

use app\models\forms\AdTemplateForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\CampaignTemplate;

/* @var $this yii\web\View */
/* @var $model AdTemplateForm */
/* @var $form yii\widgets\ActiveForm */


$templates = CampaignTemplate::find()->andWhere(['shop_id' => $model->shop_id])->all();

\app\assets\ChosenAsset::register($this);
$selectedBrandIds = $model->getSelectedBrandIds();

?>

<div class="template-form">

    <p>
        Доступные placeholder'ы: [brand], [category], [price], [title], [extTitle]
    </p>

    <?php $form = ActiveForm::begin([
        'id' => 'template-form-id',
        'enableClientScript' => false,
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'message')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'campaignTemplateIds')
        ->dropDownList(ArrayHelper::map($templates, 'id', 'title'), [
            'multiple' => true,
            'data-placeholder' => 'Выберите шаблоны'
        ]) ?>

    <?= $form->field($model, 'sort')?>

    <div class="row">
        <div class="col-sm-6">
            <?=$form->field($model, 'price_from')->label('Цена с ')?>
        </div>
        <div class="col-sm-6">
            <?=$form->field($model, 'price_to')->label('Цена по')?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
        <div class="form-group" style="height: 500px; overflow: hidden; overflow-y: scroll;">
            <label class="control-label">Категории</label>
            <div id="categories-tree">

            </div>
        </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Бренды</label>
                <div style="height: 500px; overflow: hidden; overflow-y: scroll;">
                    <?= Html::hiddenInput($model->formName() . "[brandsList]", '')?>
                    <div class="brands-list">
                        <div class="checkbox">
                            <label>
                                <?= Html::checkbox('brandIds[]', true, ['value' => '0', 'class' => 'brand-checkbox all-brand-checkbox'])?>
                                <span class="brand-title">Все</span>
                            </label>
                        </div>
                        <?foreach ($model->getBrandsList() as $brand):?>
                            <div class="checkbox">
                                <label>
                                    <?= Html::checkbox('AdTemplateForm[brandIds][]', (in_array($brand['id'], $selectedBrandIds) || $model->isNew()), ['value' => $brand['id'], 'class' => 'brand-checkbox', 'data-brand-id' => $brand['id']])?>
                                    <span class="brand-title"><?= $brand['title'] ?></span>
                                </label>
                            </div>
                        <?endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?if ($model->hasErrors('brandIds')): ?>
    <div class="form-group has-error">
        <div class="help-block"><?=$model->getFirstError('brandIds')?></div>
    </div>
    <?endif;?>

    <div class="hidden-inputs-container"></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'id' => 'save-button'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

\app\assets\JsTreeAsset::register($this);

$categoriesJson = json_encode($model->getCategoriesTree());

$this->registerJs("
$('#categories-tree').jstree({
    core: {
        data: $categoriesJson
    },
    plugins : [ 'wholerow', 'checkbox' ]
});

$('.template-form').on(\"change\", \".brand-checkbox\", function(e) {
        var \$this = $(this),
            val = \$this.val(),
            \$container = \$this.closest(\".brands-list\"),
            checkedCount = \$container.find(\".brand-checkbox:checked:not(.all-brand-checkbox)\").length,
            checkboxCount = \$container.find(\".brand-checkbox\").length - 1,
            isChecked = \$this.is(\":checked\");

        if (val == 0) {
            \$this.closest(\".checkbox\").siblings().find(\".brand-checkbox\").prop(\"checked\", isChecked);
        } else {
            \$container.find(\".all-brand-checkbox\").prop(\"checked\", checkedCount >= checkboxCount);
        }
    }
);

var checkedCount = $(\".brands-list\").find(\".brand-checkbox:checked:not(.all-brand-checkbox)\").length,
    checkboxCount = $(\".brands-list\").find(\".brand-checkbox\").length - 1;
    
$('.all-brand-checkbox').prop('checked', checkedCount == checkboxCount);

");

$this->registerJs("$('#adtemplateform-campaigntemplateids').chosen();");

$this->registerJs('
$("#template-form-id").on("submit", function (e) {
    var $container = $(".hidden-inputs-container"),
        selectedCategoriesIds = $.jstree.reference("#categories-tree").get_checked();
    $container.html("");
    
    selectedCategoriesIds.forEach(function(item) {
        $("<input>").attr({
            type: "hidden",
            name: "AdTemplateForm[categoryIds][]"
        }).val(item).appendTo($container);
    });
});
');