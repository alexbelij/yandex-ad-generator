<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\SitelinksItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Быстрые ссылки';
$this->params['breadcrumbs'][] = ['label' => 'Список', 'url' => ['/sitelinks']];

if ($dataProvider->totalCount >= 4) {
    $addButtonIsDisabled = true;
} else {
    $addButtonIsDisabled = false;
}

?>
<div class="sitelinks-item-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?if ($addButtonIsDisabled):?>
            <?= Html::button('Новая ссылка', ['class' => 'btn btn-success', 'disabled' => true]) ?>
        <?else:?>
            <?= Html::a('Новая ссылка', ['create', 'sitelinkId' => Yii::$app->request->get('sitelinkId')], ['class' => 'btn btn-success']) ?>
        <?endif;?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => \yii\grid\SerialColumn::className()],
            'title',
            'href',
            'description',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
