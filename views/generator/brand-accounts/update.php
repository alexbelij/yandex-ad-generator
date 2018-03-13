<?php

use yii\helpers\Html;
use app\models\BrandAccount;

/* @var $this yii\web\View */
/* @var $model BrandAccount */

$this->title = $model->brandTitle;
?>
<div class="account-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
