<?php

use app\helpers\ArrayHelper;
use app\modules\bidManager\models\Strategy;
use app\modules\bidManager\models\YandexBid;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\bidManager\models\search\YandexBidSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ставки';
$this->params['breadcrumbs'][] = $this->title;

$strategies = ArrayHelper::map(Strategy::find()->all(), 'id', 'strategy');
array_unshift($strategies, 'не задано');

?>
<div class="yandex-bid-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'adGroup.name',
                'label' => 'Группа',
                'filter' => Html::activeInput('text', $searchModel, 'group_name', ['class' => 'form-control'])
            ],
            [
                'attribute' => 'keyword.keyword',
                'filter' => Html::activeInput('text', $searchModel, 'keyword', ['class' => 'form-control'])
            ],
            'bid',
            'bid_min_search_price',
            'bid_current_search_price',
            [
                'attribute' => 'bidAuction.spec1_bid',
                'label' => 'СР1'
            ],
            [
                'attribute' => 'bidAuction.spec2_bid',
                'label' => 'СР2'
            ],
            [
                'attribute' => 'bidAuction.spec3_bid',
                'label' => 'СР3'
            ],

            [
                'attribute' => 'bidAuction.gar1_bid',
                'label' => 'ГАР1'
            ],
            [
                'attribute' => 'bidAuction.gar2_bid',
                'label' => 'ГАР2'
            ],
            [
                'attribute' => 'bidAuction.gar3_bid',
                'label' => 'ГАР3'
            ],
            [
                'attribute' => 'bidAuction.gar4_bid',
                'label' => 'ГАР4'
            ],
            [
                'attribute' => 'keyword.max_click_price',
                'format' => 'raw',
                'value' => function (YandexBid $model) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'max_click_price-' . $model->keyword->primaryKey,
                        ],
                        'model' => $model->keyword,
                        'attribute' => 'max_click_price',
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/keywords/ajax-update', 'id' => $model->keyword->primaryKey])
                        ],
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'filter' => Html::activeInput('text', $searchModel, 'max_click_price', ['class' => 'form-control'])
            ],
            [
                'attribute' => 'keyword.strategy_1',
                'format' => 'raw',
                'value' => function (YandexBid $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy_1-' . $model->keyword->primaryKey,
                        ],
                        'model' => $model->keyword,
                        'attribute' => 'strategy_1',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'displayValueConfig' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/keywords/ajax-update', 'id' => $model->keyword->primaryKey])
                        ],
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'strategy_1',
                    $strategies,
                    ['class' => 'form-control', 'prompt' => 'Все']
                )
            ],
            [
                'attribute' => 'keyword.strategy_2',
                'format' => 'raw',
                'value' => function (YandexBid $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy_2-' . $model->keyword->primaryKey,
                        ],
                        'model' => $model->keyword,
                        'attribute' => 'strategy_2',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'displayValueConfig' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/keywords/ajax-update', 'id' => $model->keyword->primaryKey])
                        ],
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'strategy_2',
                    $strategies,
                    ['class' => 'form-control', 'prompt' => 'Все']
                )
            ]
        ],
    ]); ?>
</div>
