<?php

use app\lib\services\BrandCountService;
use app\models\AdYandexGroup;
use app\models\Product;
use app\models\Variation;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use app\models\search\ProductsSearch;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use app\assets\KeywordsAsset;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use kartik\editable\Editable;
use yii\data\ActiveDataProvider;
use app\models\Ad;

/** @var View $this */
/** @var ProductsSearch $searchModel */
/** @var array $brands */
/** @var BrandCountService $brandCountService */
/** @var array $categoriesTree */

$this->title = 'Ключевые слова и заголовки';

Pjax::begin(['id' => 'keywords-container-pjax']);
$form = ActiveForm::begin([
    'id' => 'keywords-filter-form',
    'method' => 'get',
    'action' => Url::to(['/generator/keywords', 'shopId' => $searchModel->shopId]),
    'options' => [
        'data-pjax' => '1'
    ]
]);

$brandsList = ArrayHelper::map($brands, 'id', 'title');
foreach ($brandsList as $brandId => $title) {
    $brandsList[$brandId] = $title . ' (' . $brandCountService->getCount($brandId) . '/' . $brandCountService->getCountByFilter($brandId) . ')';
}

?>

<style>
    textarea.kv-editable-input {
        width: 350px !important;
        height: 102px !important;
    }

    .button-link.kv-editable-value {
        display: inline-block;
        padding: 3px 10px;
        margin-bottom: 5px;
        font-size: 14px;
        font-weight: normal;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;

        color: #000;
        background-color: #E2E4E6;
        border-color: #E2E4E6;
    }

    .kv-editable-link:hover {
        color: #A6A6A7;
    }

</style>

    <div class="row">
        <div class="col-sm-3">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#brands" aria-controls="home" role="tab" data-toggle="tab">Бренды</a></li>
                <li role="presentation"><a href="#categories" aria-controls="profile" role="tab" data-toggle="tab">Категории</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="brands">
                    <?= $form->field($searchModel, 'brandId')
                        ->dropDownList($brandsList, ['prompt' => 'Выберите бренд'])
                        ->label('Бренд')
                    ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="categories">
                    <div class="form-group" style="height: 500px; overflow: hidden; overflow-y: scroll;">
                        <div id="categories-tree">

                        </div>
                        <div id="hidden-input-categories"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-sm-2">
            <div style="margin-top: 30px">
                <?= $form->field($searchModel, 'onlyActive')->checkbox()->label('Только активные') ?>
            </div>
            <?= $form->field($searchModel, 'isRequireVerification')->checkbox() ?>
            <?= $form->field($searchModel, 'withoutAd')->checkbox()->label('Без заголовков') ?>
            <div class="dropdown">
                <?= Html::a('Пометить как...', '#', [
                    'class' => 'dropdown-toggle',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'true',
                    'id' => 'mark-ad-as'
                ]) ?>
                <ul class="dropdown-menu" aria-labelledby="mark-ad-as">
                    <li><?= Html::a('Не требует проверки', '#', [
                            'class' => 'verify',
                            'data-shop-id' => $searchModel->shopId,
                            'data-verify' => '0'
                        ]) ?></li>
                    <li><?= Html::a('Требует проверки', '#', [
                            'class' => 'verify',
                            'data-shop-id' => $searchModel->shopId,
                            'data-verify' => '1'
                        ]) ?></li>
                </ul>
            </div>

            <div class="dropdown">
                <?= Html::a('Сгенерировать фразы и заголовки', '#', [
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'true',
                    'id' => 'generate-ad'
                ]) ?>
                <ul class="dropdown-menu" aria-labelledby="mark-ad-as">
                    <li><?= Html::a('Фразы и заголовки', '#', [
                            'class' => 'ad-generate',
                            'data-shop-id' => $searchModel->shopId,
                            'data-type' => 'all'
                        ]) ?></li>
                    <li><?= Html::a('Только фразы', '#', [
                            'class' => 'ad-generate',
                            'data-shop-id' => $searchModel->shopId,
                            'data-type' => 'keywords'
                        ]) ?></li>
                </ul>
            </div>

        </div>
        <div class="col-sm-2">
            <?= $form->field($searchModel, 'title')->label('Название товара') ?>
            <?= $form->field($searchModel, 'dateFrom')->widget(\kartik\date\DatePicker::className()) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($searchModel, 'adTitle')->label('Заголовок') ?>
            <?= $form->field($searchModel, 'dateTo')->widget(\kartik\date\DatePicker::className()) ?>
            <div class="form-group">
                <?= Html::checkbox($searchModel->formName() . '[dateFilterType]', true, ['value' => 'product', 'style' => 'display: none;']) ?>
                <?= Html::checkbox($searchModel->formName() . '[dateFilterType]', $searchModel->dateFilterType == 'ad', [
                    'value' => 'ad',
                    'data-toggle' => 'toggle',
                    'data-on' => 'Объявления',
                    'data-off' => 'Товары',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'id' => 'date-filter-type'
                ]) ?>
                <!--            <input type="checkbox"  data-toggle="toggle" data-on="Объявления" data-off="Товары" data-onstyle="success" data-offstyle="danger">-->
            </div>

        </div>
        <div class="col-sm-2">
            <?= Html::submitButton('Найти', [
                'class' => 'btn btn-success',
                'style' => 'margin-top: 20px;'
            ]) ?>
        </div>
        <div class="col-sm-1">

            <div>
                <div class="pull-right" style="margin-top: 15px;">
                    <?= Html::a('Экспорт в xls', ['/generator/keywords/export-to-xls', 'shopId' => $searchModel->shopId], [
                        'class' => 'btn btn-primary export-to-xls',
                        'data-pjax' => '0'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
<? $form->end() ?>

<? Pjax::begin([
    'id' => 'keywords-grid-pjax',
    'timeout' => 15000
]) ?>
    <div class="row">
        <div class="col-sm-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'label' => 'Название товара',
                        'attribute' => 'title',
                        'value' => function ($model) use ($searchModel) {
                            echo Html::hiddenInput("Products[{$model['id']}][title]", $model['title']);
                            echo Html::hiddenInput("Products[{$model['id']}][brand_id]", $model['brand']['id']);
                            echo Html::hiddenInput("Products[{$model['id']}][is_available]", $model['is_available']);
                            echo Html::hiddenInput("Products[{$model['id']}][price]", $model['price']);
                            echo Html::hiddenInput("Products[{$model['id']}][shop_id]", $searchModel->getShop()->primaryKey);
                            echo Html::hiddenInput("Products[{$model['id']}][category_id]", ArrayHelper::getValue($model, 'categories.0.id'));

                            if (!empty($model['type_prefix'])) {
                                $catPrefix = $model['type_prefix'];
                            } else {
                                $catPrefix = $model['categories'][0]['title'];
                            }

                            $productModel = null;
                            if (!empty($model['our_id'])) {
                                $productModel = Product::findOne($model['our_id']);
                            }

                            $isDuplicate = ArrayHelper::getValue($productModel, 'is_duplicate');
                            $duplicateMsg = '';
                            if ($isDuplicate) {
                                $duplicateMsg = ' ' . Html::tag('b', Html::tag('i', 'дубль'));
                            }
                            $editIcon = '';

                            if ($searchModel->getShop()->external_strategy == \app\models\Shop::EXTERNAL_STRATEGY_YML) {
                                $editIcon = Html::a(
                                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-pencil']),
                                    ['/external-product/update', 'id' => $model['id']],
                                    ['data-pjax' => '0']
                                );
                            }

                            $content = Html::tag('div', Html::encode($catPrefix . ' ' . $model['brand']['title'] . ' ' . $model['title']) . $duplicateMsg .
                                ' ' . Html::a('(на сайте)', $model['href'], ['target' => '_blank', 'data-pjax' => '0']) . ' ' . $editIcon);

                            if ($searchModel->getShop()->external_strategy == \app\models\Shop::EXTERNAL_STRATEGY_YML) {
                                $link = Html::a(
                                    'Вариации категории - ' . $model['categories'][0]['title'],
                                    [
                                        '/generator/variations',
                                        'type' => Variation::TYPE_CATEGORY,
                                        'shopId' => $searchModel->shopId,
                                        'CategoryVariationSearch[ids]' => $model['categories'][0]['id']
                                    ],
                                    [
                                        'target' => '_blank',
                                        'data-pjax' => '0'
                                    ]
                                );
                                $content .= Html::tag('div', $link);
                                /** @var Variation $categoryVariation */
                                $categoryVariation = Variation::find()
                                    ->andWhere([
                                        'shop_id' => $searchModel->shopId,
                                        'entity_type' => Variation::TYPE_CATEGORY,
                                        'entity_id' => $model['categories'][0]['id']
                                    ])->one();

                                if ($categoryVariation) {
                                    $content .= Html::ul($categoryVariation->getVariationList(false));
                                }
                            }

                            if (!empty($model['type_prefix'])) {
                                $content .= Html::tag('div', 'Type prefix - ' . $model['type_prefix']);
                            }

                            return $content;
                        },
                        'format' => 'raw',
                        'headerOptions' => [
                            'width' => '30%'
                        ]
                    ],
                    [
                        'label' => 'Объявления',
                        'headerOptions' => [
                            'width' => '50%'
                        ],
                        'contentOptions' => [
                            'style' => 'background-color: #FFEBCD;'
                        ],
                        'value' => function ($product) use ($searchModel) {
                            $dataProvider = new ActiveDataProvider([
                                'query' => Ad::find()
                                    ->joinWith('product')
                                    ->andWhere([
                                        'product.id' => $product['our_id'],
                                        'shop_id' => $searchModel->shopId,
                                        'is_deleted' => false
                                    ])
                            ]);

                            $adGrid = GridView::widget([
                                'options' => [
                                    'class' => 'ads-grid',
                                    'data' => [
                                        'id' => $product['our_id'],
                                        'product-id' => $product['id']
                                    ],
                                ],
                                'layout' => '{items}',
                                'dataProvider' => $dataProvider,
                                'columns' => [
                                    [
                                        'label' => 'Заголовок',
                                        'headerOptions' => [
                                            'width' => '40%',
                                        ],
                                        'value' => function (Ad $model) use ($product, $searchModel) {

                                            $output = Editable::widget([
                                                'id' => 'edit-keyword-ad-title-' . $model->primaryKey,
                                                'name' => 'Ad[title]',
                                                'value' => $model->title,
                                                'header' => 'Изменить ключевое слово',
                                                'asPopover' => false,
                                                'ajaxSettings' => [
                                                    'url' => Url::to(['/generator/keywords/update-title', 'adId' => $model->primaryKey])
                                                ],
                                                'pjaxContainerId' => "ad-pjax-{$product['id']}",
                                                'editableValueOptions' => [
                                                    'class' => 'button-link'
                                                ],
                                                'submitButton' => [
                                                    'class' => 'btn btn-sm btn-primary',
                                                    'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                                                ]
                                            ]);

                                            $isRequireVerification = Html::tag('div',
                                                Html::label(
                                                    Html::checkbox(
                                                        $model->formName() . "[{$model->id}][is_require_verification]",
                                                        $model->is_require_verification
                                                    ) . $model->getAttributeLabel('is_require_verification')
                                                ), [
                                                    'class' => 'checkbox require-verification',
                                                    'data-ad-id' => $model->primaryKey
                                                ]);

                                            $shuffleGroups = '';
                                            if ($searchModel->getShop()->is_shuffle_groups) {
                                                foreach ($model->yandexAds as $yandexAd) {
                                                    $inputShuffleId = 'shuffle-' . $yandexAd->id;
                                                    $shuffleGroups .= Html::tag('div',
                                                        Html::label(
                                                            Html::checkbox(
                                                                $model->formName() . "[{$model->id}][{$yandexAd->id}][rarely_served]",
                                                                $yandexAd->adYandexGroup->serving_status == AdYandexGroup::SERVING_STATUS_RARELY_SERVED,
                                                                ['id' => $inputShuffleId, 'class' => 'shuffle-groups', 'data-yandex-campaign-id' => $yandexAd->id]
                                                            ) . $yandexAd->yandexCampaign->title . ' (' . $yandexAd->adYandexGroup->status . ')'
                                                        ), ['class' => 'checkbox']
                                                    );
                                                }
                                            }


                                            $generationDate = Html::tag('div',
                                                'Дата/время генерации: ' .
                                                Html::tag('span', Yii::$app->formatter->asDate($model->generated_at, 'php:d.m.Y H:i'), ['style' => 'color: #445287;'])
                                            );

                                            if (!empty($shuffleGroups)) {
                                                $shuffleGroups = Html::tag('div',
                                                    Html::tag('div', 'Мало показов', ['style' => 'font-weight: bold; margin-top: 6px;']) .
                                                    Html::tag('div', $shuffleGroups)
                                                );
                                            }

                                            return $output . $isRequireVerification . $generationDate . $shuffleGroups;
                                        },
                                        'format' => 'raw'
                                    ],
                                    [
                                        'label' => 'Ключевые слова',
                                        'headerOptions' => [
                                            'width' => '40%'
                                        ],
                                        'value' => function (Ad $model) use ($product) {
                                            $keywordElements = '';
                                            $keywords = ArrayHelper::map($model->adKeywords, 'id', 'keyword');
                                            foreach ($keywords as $keywordId => $keyword) {
                                                $keywordElement = Editable::widget([
                                                    'id' => 'edit-keyword-ad-' . $keywordId,
                                                    'name' => 'keyword',
                                                    'value' => $keyword,
                                                    'header' => 'Изменить ключевое слово',
                                                    'asPopover' => false,
                                                    'ajaxSettings' => [
                                                        'url' => Url::to(['/generator/keywords/update-keyword', 'adId' => $model->primaryKey])
                                                    ],
                                                    'afterInput' => Html::hiddenInput('old_keyword', $keyword),
                                                    'pjaxContainerId' => "ad-pjax-{$product['id']}",
                                                    'editableValueOptions' => [
                                                        'class' => 'button-link'
                                                    ],
                                                    'submitButton' => [
                                                        'class' => 'btn btn-sm btn-primary',
                                                        'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                                                    ]
                                                ]);

                                                $link = Html::a(Html::tag('i', '', ['class' => 'glyphicon glyphicon-trash']), '#', [
                                                    'class' => 'delete-keyword',
                                                    'data-keyword' => $keyword,
                                                    'data-ad-id' => $model->id,
                                                    'style' => 'margin-left: 10px;'
                                                ]);
                                                $keywordElements .= Html::tag('div', $keywordElement . $link, ['class' => 'keyword-item']);
                                            }

                                            $newKeywordsBtn = Editable::widget([
                                                'id' => 'new-keywords-ad-' . $model->id,
                                                'name' => 'newKeywords',
                                                'value' => '',
                                                'inputType' => Editable::INPUT_TEXTAREA,
                                                'asPopover' => false,
                                                'header' => 'Добавление ключевых фраз',
                                                'displayValue' => 'Добавить фразы',
                                                'submitOnEnter' => false,
                                                'size' => 'lg',
                                                'ajaxSettings' => [
                                                    'url' => Url::to(['/generator/keywords/add-keywords', 'adId' => $model->primaryKey])
                                                ],
                                                'pjaxContainerId' => "ad-pjax-{$product['id']}",
                                                'pluginEvents' => [
                                                    'editableSuccess' => "
                                                        function () {
                                                            $.pjax.reload('#ad-pjax-{$product['id']}');
                                                        }
                                                    "
                                                ],
                                            ]);

                                            return $keywordElements .
                                                Html::tag('div', $newKeywordsBtn, ['style' => 'float: right;']) . Html::tag('div', '', ['style' => 'clear: both;']);
                                        },
                                        'format' => 'raw'
                                    ],
                                    [
                                        'class' => \yii\grid\ActionColumn::className(),
                                        'headerOptions' => [
                                            'width' => '10%'
                                        ],
                                        'header' => \yii\helpers\Html::button('Добавить', ['class' => 'btn btn-success add-ad']),
                                        'template' => '{delete}',
                                        'buttons' => [
                                            'delete' => function ($url, Ad $model, $key) {
                                                $url = Url::to(['/generator/keywords/remove-ad', 'adId' => $model->primaryKey]);

                                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                                    'title' => Yii::t('yii', 'Delete'),
                                                    'aria-label' => Yii::t('yii', 'Delete'),
                                                    'data-pjax' => '1',
                                                    'class' => 'remove-ad',
                                                    'data-ad-id' => $model->primaryKey
                                                ]);
                                            }
                                        ]
                                    ]
                                ]
                            ]);

                            ob_start();
                            Pjax::begin([
                                'id' => 'ad-pjax-' . $product['id'],
                                'timeout' => 15000
                            ]);
                            echo $adGrid;
                            Pjax::end();

                            return ob_get_clean();
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => 'Цена',
                        'attribute' => 'price',
                        'value' => function ($model) {
                            return Editable::widget([
                                'format' => Editable::FORMAT_LINK,
                                'asPopover' => true,
                                'inputType' => Editable::INPUT_TEXT,
                                'value' => $model['manual_price'],
                                'name' => "Products[{$model['id']}][manual_price]",
                                'pjaxContainerId' => 'keywords-container-pjax',
                                'ajaxSettings' => [
                                    'method' => 'post',
                                    'url' => Url::to(['/generator/keywords/stub'])
                                ],
                                'displayValue' => $model['manual_price'] ? $model['manual_price'] . ' (manual)' : $model['price'],
                                'afterInput' => function ($form) use ($model) {
                                    echo Html::hiddenInput('field', 'manual_price');
                                    echo Html::hiddenInput("Products[{$model['id']}][price]", $model['price']);
                                },
                            ]);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => 'Наличие',
                        'attribute' => 'is_available',
                        'value' => function ($model) {
                            $class = $model['is_available'] ? "glyphicon glyphicon-plus" : "glyphicon glyphicon-minus";
                            return Html::tag('i', '', ['class' => $class]);
                        },
                        'format' => 'html'
                    ],
                    [
                        'label' => 'Дата появления товара',
                        'value' => function ($model) {
                            return Yii::$app->formatter->format($model['created_at'], ['date', 'php:Y-m-d']);
                        },
                        'format' => 'html'
                    ]
                ]
            ]) ?>
        </div>
    </div>

<? Pjax::end() ?>

<? $modal = \yii\bootstrap\Modal::begin([
    'id' => 'generator-modal',
    'header' => '<h4><span class="type-title"> Генерация заголовков</span></h4>',
    'footer' => "
                <div class='btn-group'>
                    <button type=\"button\" class=\"btn btn-primary dropdown-toggle\" data-toggle=\"dropdown\">Генерация <span class=\"caret\"></span></button>
                    <ul class=\"dropdown-menu\" role=\"menu\">
                      <li><a href=\"#\" class='run-generator' data-generator-option='overwrite-all'>Перезаписать</a></li>
                      <li><a href=\"#\" class='run-generator' data-generator-option='overwrite'>Оставить добавленные вручную</a></li>
                      <li><a href=\"#\" class='run-generator' data-generator-option='leave'>Оставить существующие</a></li>
                    </ul>
                </div>
                <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Закрыть</button>"
])?>

    <div>
        Запуск генерации ключевых слов и заголовков.
    </div>

<? $modal->end()?>

<?php

\app\assets\BootstrapToggleAsset::register($this);
\app\assets\JsTreeAsset::register($this);

$categoriesJson = json_encode($categoriesTree);

$this->registerJs("
    $(function() {
        $('#date-filter-type').bootstrapToggle();
    });
    $('#categories-tree').jstree({
        core: {
            data: $categoriesJson
        },
        plugins : [ 'wholerow', 'checkbox' ]
    });
    
    $('#keywords-filter-form').submit(function(e) {
        var selectedCategoriesIds = $.jstree.reference('#categories-tree').get_checked(),
            \$hiddenInputsArea = $('#hidden-input-categories');
        
        \$hiddenInputsArea.html('');
        
        \$hiddenInputsArea.append('<input type=\"hidden\" name=\"ProductsSearch[categoryId]\" value=\"' + selectedCategoriesIds.join(',') + '\">');
//        selectedCategoriesIds.forEach(function(categoryId) {
//            
//        });
        
    });
");

Pjax::end();

KeywordsAsset::register($this);
\kartik\editable\EditableAsset::register($this);
\kartik\editable\EditablePjaxAsset::register($this);
\kartik\popover\PopoverXAsset::register($this);