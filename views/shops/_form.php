<?php

use app\models\FileImport;
use app\models\Shop;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Shop */
/* @var $form yii\widgets\ActiveForm */

if (empty($model->external_strategy)) {
    $model->external_strategy = $model::EXTERNAL_STRATEGY_API;
}

$accounts = \app\models\Account::find()->all();

if (empty($model->shuffle_strategy)) {
    $model->shuffle_strategy = Shop::SHUFFLE_STRATEGY_DEFAULT;
}

?>

<div class="shop-form">

    <?php $form = ActiveForm::begin(); ?>

    <?=Html::hiddenInput('Shop[is_import_schedule]', 0)?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_id')
        ->dropDownList(\app\helpers\ArrayHelper::map($accounts, 'id', 'title'), [
            'data-placeholder' => 'Выберите аккаунт',
            'prompt' => ''
        ])
    ?>

    <?= $form->field($model, 'external_strategy')
        ->radioList(
            Shop::getAvailableExternalStrategies(), [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $content = Html::radio($name, $checked, ['value' => $value]);
                    $content = Html::label($content . $label);
                    return Html::tag('div', $content, ['class' => 'radio']);
                }
            ]) ?>

    <div class="strategy-fields row" data-strategy="yml,xls">
        <div class="col-sm-12">
            <?= $form->field($model, 'is_import_schedule')->checkbox()?>
        </div>
        <div class="schedule-fields">
            <div class="col-sm-6">
                <?= $form->field($model, 'remote_file_url')->textInput()?>
            </div>
            <div class="col-sm-3">
                <?=$form->field($model, 'schedule')->textInput()?>
            </div>
        </div>
        <div class="col-sm-12 strategy-fields" data-strategy="yml">
            <?=$form->field($model, 'strategy_factory')->dropDownList(FileImport::getStrategiesList())?>
        </div>
    </div>

    <div class="api-settings strategy-fields" data-strategy="api">
        <?= $form->field($model, 'brand_api_url')->textInput() ?>

        <?= $form->field($model, 'product_api_url')->textInput() ?>

        <?= $form->field($model, 'category_api_url')->textInput() ?>

        <?= $form->field($model, 'api_secret_key')->textInput() ?>
    </div>

    <?= $form->field($model, 'variation_strategy')->dropDownList(Shop::getVariationGenerationStrategies())?>

    <?= $form->field($model, 'href_template')?>

    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'is_autoupdate')->checkbox() ?>
        </div>
        <div class="col-sm-4">
            <?
            $options = [];
            if (!$model->is_autoupdate) {
                $options = ['style' => 'display: none;'];
            }
            ?>
            <?= $form->field($model, 'schedule_autoupdate', ['options' => $options])->textInput()->label(false)?>
        </div>
    </div>

    <?=$form->field($model, 'is_link_validation')->checkbox()?>
    <?=$form->field($model, 'is_shuffle_groups')->checkbox()?>

    <?php

    $options = [];

    if (!$model->is_shuffle_groups) {
        $options['style'] = 'display: none;';
    }

    echo $form->field($model, 'shuffle_strategy', ['options' => $options])->radioList(Shop::getShuffleStrategies(), [
        'item' => function ($index, $label, $name, $checked, $value) {
            $content = Html::radio($name, $checked, ['value' => $value]);
            $content = Html::label($content . $label);
            return Html::tag('div', $content, ['class' => 'radio']);
        }
    ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Отмена', \yii\helpers\Url::to(['/shops']), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

\app\assets\ChosenAsset::register($this);

$this->registerJs("
(function() {
    
    function updateStrategy() {
        var externalStrategy = $('[name*=external_strategy]:checked').val(),
            isUseSchedule = $('#shop-is_import_schedule').is(':checked');
         
        $('.strategy-fields').each(function(val, i) {
            var strategies = $(this).data('strategy').split(',');
            if ($.inArray(externalStrategy, strategies) == -1) {
                $(this).hide();
                $('input', this).prop('disabled', true);
            } else {
                $(this).show();
                $('input', this).prop('disabled', false);
            }
        });
        
        $('.schedule-fields input').prop('disabled', !isUseSchedule);
        
    }
    
    $('[name*=external_strategy]').click(updateStrategy);
    $('#shop-is_import_schedule').change(updateStrategy);
    
    updateStrategy();
    
    $('#shop-schedule, #shop-schedule_autoupdate').inputmask('Regex', {
        regex: \"[a-zA-Z0-9,/*-]+ [a-zA-Z0-9,/*-]+ [a-zA-Z0-9,/*-]+ [a-zA-Z0-9,/*-]+ [a-zA-Z0-9,/*-]+\"
    });
    
    $('#shop-is_autoupdate').change(function(e) {
        var \$this = $(this),
            \$scheduleContainer = $('.field-shop-schedule_autoupdate');
        
        if (\$this.is(':checked')) {
            \$scheduleContainer.show();
        } else {
            \$scheduleContainer.hide();
        }
    });
    
    $('#shop-is_shuffle_groups').change(function(e) {
        var \$this = $(this),
            \$scheduleContainer = $('.field-shop-shuffle_strategy');
        
        if (\$this.is(':checked')) {
            \$scheduleContainer.show();
        } else {
            \$scheduleContainer.hide();
        }
    });
    
    $('#shop-account_id').chosen({
        no_results_text: \"Аккаунты не найдены!\",
        placeholder_text_single: 'Выберите аккаунт'
    });
    
}());
");