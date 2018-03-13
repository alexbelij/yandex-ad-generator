<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ExternalProduct */

$this->title = 'Create External Product';
$this->params['breadcrumbs'][] = ['label' => 'External Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
