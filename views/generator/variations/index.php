<?php

use kartik\editable\Editable;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\search\VariationSearch;
use app\lib\provider\ApiDataProvider;
use app\models\Variation;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var \yii\web\View $this
 * @var ApiDataProvider $dataProvider
 * @var VariationSearch $searchModel
 * @var string $variationName
 */

$this->title = 'Управление вариациями - ' . $variationName;

?>

<style type="text/css">
    .button-link.kv-editable-value  {
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
    <div class="col-sm-12">
        <h2><?= Html::encode($this->title)?></h2>
    </div>
</div>
<? Pjax::begin([
    'id' => 'variation-pjax',
    'timeout' => 15000
])?>
<?
$form = ActiveForm::begin([
    'method' => 'get',
    'action' => Url::to(['/generator/variations', 'shopId' => $searchModel->shopId, 'type' => $searchModel->getType()]),
    'options' => [
        'data-pjax' => 1,
        'class' => 'form-inline'
    ]
]);
?>
<div class="row">
    <div class="col-sm-3">
        <div style="margin-top: 30px">
            <?= $form->field($searchModel, 'name')->textInput(['class' => 'submit-on-change form-control'])?>
        </div>
    </div>
    <div class="col-sm-2">
        <div style="margin-top: 30px">
            <?= $form->field($searchModel, 'onlyActive')->checkbox(['class' => 'submit-on-change'])?>
        </div>
    </div>

</div>
<? $form->end()?>


<div class="row">
    <div class="col-sm-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'label' => 'Ид',
                    'attribute' => 'entity_id'
                ],
                'title:text:Название',
                [
                    'label' => 'Вариации',
                    'value' => function (Variation $model) {

                        if (empty($model->variationItems)) {
                            $variations = [$model->title];
                        } else {
                            $variations = $model->variationItems;
                        }

                        $variationElements = '';
                        foreach ($variations as $variation) {
                            if (is_object($variation)) {
                                $isUseInGeneration = $variation->is_use_in_generation;
                                $value = $variation->value;
                                $variationItemId = $variation->id;
                            } else {
                                $value = $variation;
                                $isUseInGeneration = true;
                                $variationItemId = 0;
                            }
                            $variationElement = Editable::widget([
                                'id' => 'edit-variation-' . md5($model->entity_id . $value),
                                'name' => 'variation',
                                'value' => $value,
                                'header' => 'Изменить вариацию',
                                'asPopover' => false,
                                'pjaxContainerId' => "variation-pjax",
                                'ajaxSettings' => [
                                    'url' => Url::to([
                                        '/generator/variations/update-variation',
                                        'entityId' => $model->entity_id,
                                        'entityType' => $model->entity_type,
                                        'id' => $model->id,
                                        'shopId' => $model->shop_id
                                    ])
                                ],
                                'afterInput' => Html::tag('div', Html::tag('div',
                                    Html::tag('label',
                                        Html::checkbox('isUseInGeneration', $isUseInGeneration) . ' Использовать при генерации'
                                    )
                                    , ['class' => 'checkbox']
                                ), ['class' => 'form-group', 'style' => 'display: block;']) . Html::hiddenInput('variationItemId', $variationItemId),
                                'editableValueOptions' => [
                                    'class' => 'button-link'
                                ],
                                'submitButton' => [
                                    'class' => 'btn btn-sm btn-primary',
                                    'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                                ],
                                'pluginEvents' => [
                                    'editableSuccess' => "
                                    function () {
                                        $.pjax.reload('#variation-pjax', {scrollTo: false, push: false});
                                    }
                                "
                                ],
                            ]);

                            if ($variationItemId) {
                                $link = Html::a(Html::tag('i', '', ['class' => 'glyphicon glyphicon-trash']), '#', [
                                    'class' => 'delete-variation',
                                    'data-variation-item-id' => $variationItemId,
                                    'style' => 'margin-left: 10px;',
                                    'data-pjax' => '0'
                                ]);
                            } else {
                                $link = '';
                            }

                            $useInGeneration = '';
                            if ($isUseInGeneration) {
                                $useInGeneration = Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok']);
                            }

                            $variationElements .= Html::tag('div', $variationElement . $useInGeneration . $link);
                        }

                        $newVariationBlock = Editable::widget([
                            'id' => 'new-variation-' . md5($model->entity_id . $model->id . $model->title),
                            'name' => 'newVariation',
                            'value' => '',
                            'inputType' => Editable::INPUT_TEXTAREA,
                            'asPopover' => false,
                            'header' => 'Добавление вариации',
                            'displayValue' => 'Добавить вариации',
                            'submitOnEnter' => false,
                            'size' => 'lg',
                            'ajaxSettings' => [
                                'url' => Url::to(['/generator/variations/add-variations',
                                    'entityId' => $model->entity_id,
                                    'entityType' => $model->entity_type,
                                    'id' => $model->id,
                                    'shopId' => $model->shop_id
                                ])
                            ],
                            'submitButton' => [
                                'class' => 'btn btn-sm btn-primary',
                                'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                            ],
                            'pjaxContainerId' => "variation-pjax",
                            'afterInput' => Html::tag('div', Html::tag('div',
                                    Html::tag('label',
                                        Html::checkbox('isUseInGeneration', true) . ' Использовать при генерации'
                                    )
                                    , ['class' => 'checkbox']
                                ), ['class' => 'form-group', 'style' => 'display: block;']) . Html::hiddenInput('modelTitle', $model->title),
                            'pluginEvents' => [
                                'editableSuccess' => "
                                    function () {
                                        $.pjax.reload('#variation-pjax', {scrollTo: false, push: false});
                                    }
                                "
                            ],
                        ]);

                        $shuffleBlock = Editable::widget([
                            'id' => 'shuffle-name-variation-' . md5($model->entity_id . $model->id . $model->title),
                            'name' => 'shuffleName',
                            'value' => $model->shuffle_name,
                            'inputType' => Editable::INPUT_TEXTAREA,
                            'asPopover' => false,
                            'header' => 'Название для мало показов',
                            'displayValue' => 'Название для мало показов: ' . $model->shuffle_name,
                            'submitOnEnter' => false,
                            'size' => 'lg',
                            'ajaxSettings' => [
                                'url' => Url::to(['/generator/variations/set-shuffle-name',
                                    'id' => $model->id,
                                    'shopId' => $model->shop_id
                                ])
                            ],
                            'submitButton' => [
                                'class' => 'btn btn-sm btn-primary',
                                'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                            ],
                            'pjaxContainerId' => "variation-pjax",
                            'pluginEvents' => [
                                'editableSuccess' => "
                                    function () {
                                        $.pjax.reload('#variation-pjax', {scrollTo: false, push: false});
                                    }
                                "
                            ],
                        ]);

                        return $variationElements
                            . Html::tag('div', $newVariationBlock, ['style' => 'margin-top: 10px;'])
                            . Html::tag('div', $shuffleBlock, ['style' => 'margin-top: 10px;']);
                    },
                    'format' => 'raw'
                ],
                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {

                            if (!$model->id) {
                                return null;
                            }

                            $url = Url::to(['/generator/variations/delete', 'id' => $model->id]);

                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]);
                        }
                    ]
                ]
            ]
        ])?>
    </div>

</div>

<? Pjax::end()?>

<?php

$this->registerJs("
$(document).on('click', '.save-handler', function() {
    $('#variation-form').submit();
});
");

\kartik\editable\EditableAsset::register($this);
\kartik\editable\EditablePjaxAsset::register($this);
\kartik\popover\PopoverXAsset::register($this);
\app\assets\VariationAsset::register($this);