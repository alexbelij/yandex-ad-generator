<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ExternalBrand */

$this->title = 'Создание нового бренда';
?>
<div class="external-brand-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
