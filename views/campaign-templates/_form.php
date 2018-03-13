<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model \app\models\forms\CampaignTemplateForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $regions array */
?>

<div class="campaign-template-form">

    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false
    ]); ?>

    <div class="col-sm-12">
        <?=$form->field($model, 'title')?>

        <?= $form->field($model, 'regionList')->dropDownList(ArrayHelper::map($regions, 'GeoRegionId', 'GeoRegionName'), [
            'multiple' => true,
            'data-placeholder' => "Выберите регион..."
        ]) ?>

        <?= $form->field($model, 'minusRegionList')->dropDownList(ArrayHelper::map($regions, 'GeoRegionId', 'GeoRegionName'), [
            'multiple' => true,
            'data-placeholder' => "Выберите регион..."
        ]) ?>

        <?= $form->field($model, 'brandIds')->dropDownList($model->getBrandsList(), [
            'multiple' => true,
            'data-placeholder' => "Выберите бренд..."
        ]) ?>

        <?= $form->field($model, 'negative_keywords')->textarea(['rows' => 6]) ?>

        <div class="form-header">
            Стратегии показа
        </div>
    </div>

    <div class="col-sm-6">
        <?= $form->field($model->textCampaign, 'biddingStrategySearchType')->dropDownList($model->getTextCampaign()->getSearchStrategyList())?>
    </div>

    <div class="col-sm-6">
        <?= $form->field($model->textCampaign, 'biddingStrategyNetworkType')->dropDownList($model->getTextCampaign()->getNetworkStrategyList())?>
    </div>


    <div class="col-sm-12">
        <div class="form-header">
            Настройки
        </div>

        <div class="form-group">
            <?foreach ($model->getTextCampaign()->getSettingsList() as $name => $label):?>
                <?
                    $settingValue = ArrayHelper::getValue($model->getTextCampaign()->settings, $name);
                ?>
                <div class="checkbox">
                    <?=Html::checkbox($model->getTextCampaign()->formName() . "[settings][$name]", true, [
                        'value' => 'NO',
                        'style' => 'display: none;'
                    ])?>
                    <label>
                        <?=Html::checkbox($model->getTextCampaign()->formName() . "[settings][$name]", $settingValue == 'YES', [
                            'value' => 'YES'
                        ])?>

                        <?=$label?>
                    </label>
                </div>

            <?endforeach?>
        </div>
    </div>

    <?= $form->field($model->textCampaign, 'counterIds')?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

\app\assets\ChosenAsset::register($this);

$this->registerJs("
$('#campaigntemplateform-regionlist').chosen();
$('#campaigntemplateform-minusregionlist').chosen();
$('#campaigntemplateform-brandids').chosen();
");