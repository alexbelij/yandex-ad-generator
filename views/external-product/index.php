<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ExternalProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Редактирование товаров';
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="external-product-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'outer_id',
            'title',
            [
                'attribute' => 'brand.title',
                'label' => $searchModel->getAttributeLabel('brand_id'),
                'filter' => \kartik\widgets\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'brand_id',
                    'data' => $searchModel->getBrandsList(),
                    'language' => 'ru',
                    'options' => ['placeholder' => 'Выберите бренд ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])
            ],
            [
                'attribute' => 'category.title',
                'label' => $searchModel->getAttributeLabel('category_id'),
                'filter' => \kartik\widgets\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'category_id',
                    'data' => $searchModel->getCategoriesList(),
                    'language' => 'ru',
                    'options' => ['placeholder' => 'Выберете категорию ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])
            ],
            // 'is_available',
            // 'picture',
            // 'url:url',
            // 'currency_id',
            // 'old_price',
            // 'price',
            // 'created_at',
            // 'updated_at',
            // 'file_import_id',
            // 'model',
            'original_title',
            // 'type_prefix',
            [
                'attribute' => 'is_manual',
                'value' => function (\app\models\ExternalProduct $model) {
                    return Html::tag('i', '', ['class' => $model->is_manual ? 'glyphicon glyphicon-ok' : 'glyphicon glyphicon-minus']);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'is_generate_ad',
                'value' => function (\app\models\ExternalProduct $model) {
                    return Html::tag('i', '', ['class' => $model->is_generate_ad ? 'glyphicon glyphicon-ok' : 'glyphicon glyphicon-minus']);
                },
                'format' => 'raw'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
