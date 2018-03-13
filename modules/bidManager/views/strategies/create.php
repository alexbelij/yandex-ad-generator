<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\Strategy */

$this->title = 'Создание новой стратегии';
$this->params['breadcrumbs'][] = ['label' => 'Стратегии', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="strategy-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
