<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\Strategy */

$this->title = 'Обновление стратегии: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Стратегии', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Обновление';
?>
<div class="strategy-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
