<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\feed\models\FeedItem */

$this->title = 'Create Feed Item';
$this->params['breadcrumbs'][] = ['label' => 'Feed Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feed-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
