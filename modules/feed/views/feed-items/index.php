<?php

use app\helpers\ArrayHelper;
use app\modules\feed\models\FeedItem;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\feed\models\search\FeedItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Выгрузка фида';
$this->params['breadcrumbs'][] = $this->title;

\app\modules\feed\assets\FeedAsset::register($this);

?>
<div class="feed-item-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'brand.title',
                'label' => 'Бренд',
                'filter' => \kartik\widgets\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'brand_id',
                    'data' => ArrayHelper::map($searchModel->getBrands(), 'id', 'title'),
                    'language' => 'ru',
                    'options' => ['placeholder' => 'Выберите бренд ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])
            ],
            [
                'attribute' => 'category.title',
                'label' => 'Категория',
                'filter' => \kartik\widgets\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'category_id',
                    'data' => ArrayHelper::map($searchModel->getCategoriesList(), 'id', 'title'),
                    'language' => 'ru',
                    'options' => ['placeholder' => 'Выберите категорию...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])
            ],
            'price',
            [
                'attribute' => 'is_active',
                'value' => function (FeedItem $model) {
                    return Html::activeCheckbox($model, 'is_active', [
                        'label' => false,
                        'id' => 'feed-item-is-active-' . $model->primaryKey,
                        'class' => 'is-active',
                        'data-id' => $model->primaryKey
                    ]);
                },
                'format' => 'raw'
            ],
            'item_text:ntext',
        ],
    ]); ?>
</div>
