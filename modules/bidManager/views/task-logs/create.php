<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\TaskLog */

$this->title = 'Create Task Log';
$this->params['breadcrumbs'][] = ['label' => 'Task Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
