<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ExtrernalCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Редактирование категорий';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-category-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новая категория', ['create', 'shopId' => $searchModel->shop_id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'outer_id',
            'title',
            'original_title',
            'parent_id',
            [
                'value' => function (\app\models\ExternalCategory $model) {
                    return implode("<br/>", \app\helpers\StringHelper::explodeByDelimiter($model->variations));
                },
                'format' => 'html',
                'attribute' => 'variations'
            ],
            // 'created_at',
            // 'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
