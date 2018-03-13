<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\WordException */

$this->title = 'Create Word Exception';
$this->params['breadcrumbs'][] = ['label' => 'Word Exceptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="word-exception-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
