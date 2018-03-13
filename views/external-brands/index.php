<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ExtrernalBrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список брендов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-brand-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новый бренд', ['create', 'shopId' => Yii::$app->request->get('shopId')], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'original_title',
         //   'shop_id',
            'created_at',
            'updated_at',
            // 'outer_id',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
