<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\Feed */

$this->title = 'Новый фид';
$this->params['breadcrumbs'][] = ['label' => 'Feeds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feed-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
