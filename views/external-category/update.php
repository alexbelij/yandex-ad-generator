<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ExternalCategory */

$this->title = 'Update External Category: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'External Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="external-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
