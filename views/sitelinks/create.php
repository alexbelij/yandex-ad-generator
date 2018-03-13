<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Sitelinks */

$this->title = 'Create Sitelinks';
$this->params['breadcrumbs'][] = ['label' => 'Sitelinks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sitelinks-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
