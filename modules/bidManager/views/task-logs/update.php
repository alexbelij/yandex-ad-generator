<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\TaskLog */

$this->title = 'Update Task Log: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Task Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="task-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
