<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\QuickRedirect */

$this->title = 'Обновить редирект: ' . $model->target;
$this->params['breadcrumbs'][] = ['label' => 'Редиректы для быстрыз ссылок', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="quick-redirect-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
