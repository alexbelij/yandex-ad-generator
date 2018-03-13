<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\SitelinksItem */

$this->title = 'Create Sitelinks Item';
$this->params['breadcrumbs'][] = ['label' => 'Быстрые ссылки', 'url' => ['index', 'sitelinkId' => $model->sitelink_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sitelinks-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
