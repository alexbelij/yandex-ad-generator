<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BlackList */

$this->title = 'Update Black List: ' . ' ' . $model->name;
?>
<div class="black-list-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
