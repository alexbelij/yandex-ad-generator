<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CampaignTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $dictionaryService \app\lib\services\DictionaryService */

$this->title = 'Шаблоны кампаний ' . $searchModel->shop->name;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-template-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новый шаблон', ['create', 'shopId' => $searchModel->shop_id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
       // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            [
                'attribute' => 'regions',
                'value' => function (\app\models\CampaignTemplate $model) use ($dictionaryService) {
                    return implode(', ', $dictionaryService->getRegionTitles(explode(',', $model->regions)));
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
