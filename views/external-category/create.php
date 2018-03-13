<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ExternalCategory */

$this->title = 'Добавление новой категории';
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index', 'shopId' => $model->shop_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
