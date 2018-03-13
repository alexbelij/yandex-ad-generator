<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model \app\models\Vcard */

$this->title = 'Управление визитками';
?>
<div class="vcard-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <? if (empty($model)):?>
        <p>
            <?= Html::a('Создать визитку', ['create', 'shopId' => Yii::$app->request->get('shopId')], ['class' => 'btn btn-success']) ?>
        </p>
    <? else:?>
        <p>
            <?= Html::a('Редактирование', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        </p>
    <?endif;?>

    <?if (!empty($model)):?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'attributes' => [
                'company_name',
                'work_time',
                'phone_country_code',
                'phone_city_code',
                'phone_number',
                'phone_extension',
                'country',
                'city',
                'street',
                'house',
                'building',
                'apartment',
                'extra_message',
                'contact_email',
                'ogrn',
                'contact_person'
            ]
        ])?>
    <?else:?>
        <p>Необходимо создать визитку</p>
    <?endif;?>

</div>
