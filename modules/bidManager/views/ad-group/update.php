<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexAdGroup */

$this->title = 'Update Yandex Ad Group: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Yandex Ad Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="yandex-ad-group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
