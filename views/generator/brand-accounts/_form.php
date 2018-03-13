<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BrandAccount;
use app\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model BrandAccount */
/* @var $form yii\widgets\ActiveForm */


$accounts = \app\models\Account::find()->all();
?>

<div class="account-form">

    <?php $form = ActiveForm::begin([
        'id' => 'brand-account-form',
        'enableClientScript' => false
    ]); ?>

    <?= $form->field($model, 'account_id')->dropDownList(ArrayHelper::map($accounts, 'id', 'title'), [
        'data-placeholder' => 'Выберите аккаунт',
        'prompt' => 'Выберите аккаунт',
        'data-account-id' => $model->getOldAttribute('account_id')
    ]) ?>


    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'account-submit-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

\app\assets\ChosenAsset::register($this);

$this->registerJs('

    var $accountSelect = $("#brandaccount-account_id"),
        $accountBrandForm = $("#brand-account-form");
         
    $accountSelect.chosen();
    
    $accountBrandForm.on("submit", function (e) {
        var $this = $(this),
            prevAccountId = $accountSelect.data("accountId"),
            currentAccountId = $accountSelect.val();
            
        if ($this.data("is-success") || !prevAccountId) {
            return true;
        }
            
        if (prevAccountId && prevAccountId != currentAccountId) {
            bootbox.confirm({
                message: \'При изменении аккаунта вся информация о предыдущих размещениях бренда будет удалена! Продолжить?\',
                callback: function (result) {
                    if (result) {
                        $this.data("is-success", 1);
                        $this.submit();
                    }
                }
            });
        }
        
        e.preventDefault();
    });
');