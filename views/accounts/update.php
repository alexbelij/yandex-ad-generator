<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Account */

$this->title = 'Обновить аккаунт: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Аккаунты', 'url' => ['index']];
?>
<div class="account-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
