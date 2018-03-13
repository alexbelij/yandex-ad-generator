<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\CampaignTemplate */

$this->title = 'Новый шаблон';
$this->params['breadcrumbs'][] = ['label' => 'Шаблоны кампаний ' . $model->shop->name, 'url' => ['index', 'shopId' => $model->shop_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'regions' => $regions
    ]) ?>

</div>
