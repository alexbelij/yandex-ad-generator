<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Vcard */

$this->title = 'Обновление визитки: ' . ' ' . $model->id;
?>
<div class="vcard-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
