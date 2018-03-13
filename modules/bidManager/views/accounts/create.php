<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\Account */

$this->title = 'Новый аккаунт';
$this->params['breadcrumbs'][] = ['label' => 'Аккаунты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
