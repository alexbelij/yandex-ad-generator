<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexCampaign */

$this->title = 'Update Yandex Campaign: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="yandex-campaign-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
