<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sitelinks */

$this->title = 'Update Sitelinks: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Sitelinks', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sitelinks-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
