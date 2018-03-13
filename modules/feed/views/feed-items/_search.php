<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\search\FeedItemSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feed-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index', $model->formName() . '[feed_id]' => $model->feed_id],
        'method' => 'post',
        'id' => 'feed-search-form',
        'options' => [
            'data-pjax' => '0'
        ]
    ]); ?>

    <?=Html::hiddenInput($model->formName() . '[feed_id]', $model->feed_id, ['id' => 'feed-id'])?>

    <div id="categories-container">

    </div>

    <div class="row">
        <div class="col-sm-4">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#brands" aria-controls="home" role="tab" data-toggle="tab">Бренды</a></li>
                <li role="presentation"><a href="#categories" aria-controls="profile" role="tab" data-toggle="tab">Категории</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="brands">
                    <div class="form-group">
                        <div style="height: 500px; overflow: hidden; overflow-y: scroll;">
                            <div class="brands-list">
                                <div class="checkbox">
                                    <label>
                                        <?= Html::checkbox('brand_id[]', true, ['value' => '0', 'class' => 'brand-checkbox all-brand-checkbox'])?>
                                        <span class="brand-title">Все</span>
                                    </label>
                                </div>
                                <?foreach ($model->getBrands() as $brand):?>
                                    <div class="checkbox">
                                        <label>
                                            <?
                                            $isChecked = true;
                                            if (!empty($model->brand_id)) {
                                                $isChecked = in_array($brand['id'], (array)$model->brand_id);
                                            }
                                            ?>
                                            <?= Html::checkbox($model->formName() . '[brand_id][]', $isChecked, ['value' => $brand['id'], 'class' => 'brand-checkbox',])?>
                                            <span class="brand-title"><?= $brand['title'] ?> </span>
                                        </label>
                                    </div>
                                <?endforeach;?>
                            </div>
                        </div>
                    </div>
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
        <div class="col-sm-8">
            <div class="col-sm-6">
                <?=$form->field($model, 'priceFrom')?>
            </div>
            <div class="col-sm-6">
                <?=$form->field($model, 'priceTo')?>
            </div>
            <div class="col-sm-6">
                <?=$form->field($model, 'item_text')?>
            </div>
            <div class="col-sm-12">
                <div class="form-group pull-right">
                    <?= Html::submitButton('Скачать', ['class' => 'btn btn-success download-feed']) ?>
                    <?= Html::a('Посмотреть', ['/feed/feed/download-feed', 'feedId' => $model->feed_id, 'view' => 1], [
                        'class' => 'btn btn-success',
                        'target' => "_blank",
                        'data-pjax' => '0'
                    ]) ?>
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary search-feed']) ?>
                    <?= Html::submitButton('Поиск', ['class' => 'btn btn-warning search-feed', 'data-only-search' => 1]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php // echo $form->field($model, 'is_active') ?>

    <?php // echo $form->field($model, 'item_text') ?>


    <?php ActiveForm::end(); ?>

</div>

<?php

\app\assets\GeneralAsset::register($this);
\app\assets\JsTreeAsset::register($this);

$categoriesJson = json_encode($model->getCategories());

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