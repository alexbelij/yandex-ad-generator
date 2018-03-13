<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\QuickRedirect */

$this->title = 'Новый редирект';
$this->params['breadcrumbs'][] = ['label' => 'Редиректы для быстрых ссылок', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="quick-redirect-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
