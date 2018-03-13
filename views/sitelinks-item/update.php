<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SitelinksItem */

$this->title = 'Update Sitelinks Item: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Sitelinks Items', 'url' => ['index', 'sitelinkId' => $model->sitelink_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sitelinks-item-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
