<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignTemplate */

$this->title = 'Изменить шаблон кампании: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Шаблоны кампаний ' . $model->shop->name, 'url' => ['index', 'shopId' => $model->shop_id]];
$this->params['breadcrumbs'][] = 'Изменить шаблон кампании: ' . $model->title;
?>
<div class="campaign-template-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'regions' => $regions
    ]) ?>

</div>
