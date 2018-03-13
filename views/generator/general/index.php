<?php

use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use app\models\forms\GeneratorSettingsForm;
use app\lib\services\BrandCountService;
use app\widgets\taskButton\TaskButtonWidget;
use yii\helpers\Url;

/** @var array $brands */
/** @var GeneratorSettingsForm $model */
/** @var \app\models\TaskQueue $lastTask */
/** @var  BrandCountService $brandCountService*/

\app\assets\GeneratorAsset::register($this);

$form = ActiveForm::begin();

$this->title = 'Настройка генератора';
?>

<div class="row" id="settings-container" data-shop-id="<?=$model->shop_id?>">
    <div style="margin-bottom: 20px;">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#brands" aria-controls="home" role="tab" data-toggle="tab">Бренды</a></li>
            <li role="presentation"><a href="#categories" aria-controls="profile" role="tab" data-toggle="tab">Категории</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="brands">
                <div class="col-sm-3">
                    <div class="form-group">
                        <div style="height: 500px; overflow: hidden; overflow-y: scroll;">
                            <?= Html::hiddenInput($model->formName() . "[brandsList]", '')?>
                            <div class="brands-list">
                                <div class="checkbox">
                                    <label>
                                        <?= Html::checkbox('brandIds[]', true, ['value' => '0', 'class' => 'brand-checkbox all-brand-checkbox'])?>
                                        <span class="brand-title">Все</span>
                                    </label>
                                </div>
                                <?foreach ($model->getAvailableBrandsList() as $brand):?>
                                    <div class="checkbox">
                                        <label>
                                            <?= Html::checkbox($model->formName() . '[brandsList][]', in_array($brand['id'], $model->brandsList), ['value' => $brand['id'], 'class' => 'brand-checkbox',])?>
                                            <span class="brand-title"><?= $brand['title'] ?> (<?=$brandCountService->getCount($brand['id']) . '/' . $brandCountService->getCountByFilter($brand['id'])?>)</span>
                                        </label>
                                        <?= Html::a('Все', '#', [
                                            'class' => 'generate-keywords',
                                            'data-shop-id' => $model->shop_id,
                                            'data-brand-id' => $brand['id'],
                                            'data-type' => 'all'
                                        ])?>
                                        <?= Html::a('Фразы', '#', [
                                            'class' => 'generate-keywords',
                                            'data-shop-id' => $model->shop_id,
                                            'data-brand-id' => $brand['id'],
                                            'data-type' => 'keywords'
                                        ])?>
                                    </div>
                                <?endforeach;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="categories">
                <div class="col-sm-3">
                    <div class="form-group" style="height: 500px; overflow: hidden; overflow-y: scroll;">
                        <div id="categories-tree">

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="col-sm-9">
        <div class="row">
            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <label class="control-label">Цена</label>
                    </div>
                    <div class="col-sm-1" style="text-align: center">от</div>
                    <div class="col-sm-4">
                        <?= $form->field($model, 'price_from', ['template' => "{input}\n{error}"])->textInput()?>
                    </div>
                    <div class="col-sm-1">до</div>
                    <div class="col-sm-4">
                        <?= $form->field($model, 'price_to', ['template' => "{input}\n{error}"])->textInput()?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <?= TaskButtonWidget::widget([
                    'task' => $lastTask,
                    'caption' => 'Обновить',
                    'buttonClass' => 'show-update-form',
                    'attributes' => ['shopId' => $model->shop_id]
                ])?>

            </div>

            <div class="col-sm-3">
                <?= TaskButtonWidget::widget([
                    'task' => $lastCampaignUpdateTask,
                    'caption' => 'Обновить кампании',
                    'buttonClass' => 'campaign-update',
                    'attributes' => ['shopId' => $model->shop_id]
                ])?>
            </div>
        </div>

        <div class="row">

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= Html::a('Сохранить', '#', ['class' => 'btn btn-success save-settings'])?>
                </div>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Бренды и аккаунты', Url::to(['/generator/brand-accounts', 'shopId' => $model->shop_id]), ['class' => 'btn btn-danger'])?>
                </div>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Вариации категорий', Url::to(['/generator/variations', 'shopId' => $model->shop_id, 'type' => \app\models\Variation::TYPE_CATEGORY]), ['class' => 'btn btn-success'])?>
                </div>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Вариации брендов', Url::to(['/generator/variations', 'shopId' => $model->shop_id, 'type' => \app\models\Variation::TYPE_BRAND]), ['class' => 'btn btn-success'])?>
                </div>
                <?if ($model->shop->external_strategy == \app\models\Shop::EXTERNAL_STRATEGY_API):?>
                    <div class="col-sm-12" style="margin-top: 10px;">
                        <?= Html::a('Обновление товаров', Url::to(['/generator/general/start-update-products', 'shopId' => $model->shop_id]), ['class' => 'btn btn-danger start-update-products'])?>
                    </div>
                <?endif;?>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Запуск минусации фраз', Url::to(['/generator/general/start-minus-keywords', 'shopId' => $model->shop_id]), ['class' => 'btn btn-danger ajax-link'])?>
                </div>
                <div class="col-sm-12" style="margin-top: 10px">
                    <div class="btn-group">
                        <?= Html::button('Генерация <span class="caret"></span>', [
                            'class' => 'btn btn-primary dropdown-toggle',
                            'data-toggle' => 'dropdown',
                            'aria-haspopup' => true,
                            'aria-expanded' => false
                        ])?>
                        <ul class="dropdown-menu">
                            <li><?=Html::a('Заголовок и ключевые слова', '#', [
                                    'class' => 'generate-keywords',
                                    'data-shop-id' => $model->shop_id,
                                    'data-type' => 'all'
                                ])?></li>
                            <li><?=Html::a('Ключевые слова', '#', [
                                    'class' => 'generate-keywords',
                                    'data-shop-id' => $model->shop_id,
                                    'data-type' => 'keywords'
                                ])?></li>
                            <li><?=Html::a('Удаление дубликатов', '#', [
                                    'class' => 'remove-duplicate',
                                    'data-shop-id' => $model->shop_id,
                                ])?></li>
                            <li><?=Html::a('Удаление объявлений без вариаций брендов', '#', [
                                    'class' => 'remove-ad-without-brand',
                                    'data-shop-id' => $model->shop_id,
                                ])?></li>
                            <li><?=Html::a('Удаление автоматически сгенерированных объявлений', '#', [
                                    'class' => 'remove-auto-ads',
                                    'data-shop-id' => $model->shop_id,
                                ])?></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 50px;">
                    <?= Html::a('Отчет об отклоненных объявлениях', Url::to(['/generator/general/download-report-ads', 'shopId' => $model->shop_id]), [
                            'class' => 'btn btn-default'
                    ])?>
                </div>
                <div class="col-sm-12" style="margin-top: 5px;">
                    <?= Html::a('Отчет о недоступных товарных ссылках', Url::to(['/generator/general/download-report-product-link-fails', 'shopId' => $model->shop_id]), [
                        'class' => 'btn btn-default'
                    ])?>
                </div>
            </div>
            <div class="col-sm-6">
                <?if ($model->shop->isFileLoadStrategy()):?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="col-sm-6">
                                <?=Html::fileInput('uploadFile', null, [
                                    'accept' => '.yml,.xml,.xls,.xlsx',
                                    'class' => 'form-control'
                                ])?>
                            </div>
                            <div class="col-sm-6">
                                <?=Html::submitButton('Загрузить файл', [
                                    'class' => 'btn btn-success upload-file',
                                    'data-shop-id' => $model->shop_id
                                ])?>
                            </div>
                        </div>
                    </div>
                    <?if ($model->shop->is_import_schedule):?>
                        <div class="col-sm-12" style="margin-top: 10px;">
                            <?=Html::button('Ручной запуск импорта', [
                                'class' => 'manual-import btn btn-primary',
                                'data-shop-id' => $model->shop_id
                            ])?>
                        </div>
                    <?endif;?>
                <?endif;?>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Синхронизация объявлений', Url::to(['/generator/general/start-sync-ads', 'shopId' => $model->shop_id]), ['class' => 'btn btn-danger ajax-link'])?>
                </div>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <?= Html::a('Синхронизация ключевых фраз', Url::to(['/generator/general/start-sync-keywords', 'shopId' => $model->shop_id]), ['class' => 'btn btn-danger ajax-link'])?>
                </div>
            </div>
        </div>
    </div>
</div>

<? $modal = \yii\bootstrap\Modal::begin([
    'id' => 'generator-modal',
    'header' => '<h4><span class="type-title"> Генерация заголовков для </span><span class="brand-modal-container"></span></h4>',
    'footer' => "
                <div class='btn-group'>
                    <button type=\"button\" class=\"btn btn-primary dropdown-toggle\" data-toggle=\"dropdown\">Генерация <span class=\"caret\"></span></button>
                    <ul class=\"dropdown-menu\" role=\"menu\">
                      <li><a href=\"#\" class='run-generator' data-generator-option='overwrite-all'>Перезаписать</a></li>
                      <li><a href=\"#\" class='run-generator' data-generator-option='overwrite'>Оставить добавленные вручную</a></li>
                      <li><a href=\"#\" class='run-generator' data-generator-option='leave'>Оставить существующие</a></li>
                    </ul>
                </div>
                <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Закрыть</button>
                "
])?>

<div>
    Запуск генерации ключевых слов и заголовков.
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label class="control-label">С</label>
            <?=\kartik\widgets\DatePicker::widget(['name' => 'dateFrom'])?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="control-label">по</label>
            <?=\kartik\widgets\DatePicker::widget(['name' => 'dateTo'])?>
        </div>
    </div>
</div>

<? $modal->end()?>

<? $form->end()?>

<? $modal = \yii\bootstrap\Modal::begin([
    'id' => 'ad-update-modal',
    'options' => [
        'data-shop-id' => $model->shop_id
    ],
    'header' => Html::tag('h4', 'Обновление объявлений')
])?>

<div class="modal-container">

</div>

<? $modal->end()?>

<?php

\app\assets\GeneralAsset::register($this);
\app\assets\JsTreeAsset::register($this);

$categoriesJson = json_encode($model->getCategoriesForTree());

$this->registerJs("
$('#categories-tree').jstree({
    core: {
        data: $categoriesJson
    },
    plugins : [ 'wholerow', 'checkbox' ]
});

$('#brands').on(\"change\", \".brand-checkbox\", function(e) {
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

var checkedCount = $(\".brands-list\", '#brands').find(\".brand-checkbox:checked:not(.all-brand-checkbox)\").length,
    checkboxCount = $(\".brands-list\", '#brands').find(\".brand-checkbox\").length - 1;
    
$('.all-brand-checkbox').prop('checked', checkedCount == checkboxCount);

");