<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\bidManager\models\YandexAdGroup */

$this->title = 'Create Yandex Ad Group';
$this->params['breadcrumbs'][] = ['label' => 'Yandex Ad Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-ad-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
