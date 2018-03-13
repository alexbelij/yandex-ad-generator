<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\SitelinksSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Быстрые ссылки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sitelinks-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Sitelinks', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [

            'id',
            [
                'attribute' => 'shop_id',
                'value' => function (\app\models\Sitelinks $model) {
                    return $model->shop->name;
                }
            ],
            'title',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {items}',
                'buttons' => [
                    'items' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', \yii\helpers\Url::to(['/sitelinks-item', 'sitelinkId' => $model->id]), [
                            'title' => Yii::t('yii', 'Список элементов'),
                            'aria-label' => Yii::t('yii', 'Список элементов'),
                            'data-pjax' => '0',
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>

</div>
