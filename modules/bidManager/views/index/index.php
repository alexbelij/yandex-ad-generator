<?php

use app\helpers\ArrayHelper;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\Strategy;
use app\modules\bidManager\models\YandexCampaign;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\bidManager\models\search\YandexCampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список кампаний';
$this->params['breadcrumbs'][] = $this->title;

$strategies = ArrayHelper::map(Strategy::find()->all(), 'id', 'strategy');
array_unshift($strategies, 'не задано');

?>
<div class="yandex-campaign-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'label' => 'Аккаунт',
                'value' => function (YandexCampaign $model) {
                    return $model->account->title;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'account_id',
                    ArrayHelper::map(Account::find()->all(), 'id', 'title'),
                    ['class' => 'form-control', 'prompt' => 'Все']
                )
            ],
            [
                'attribute' => 'title',
                'format' => 'html',
                'value' => function (YandexCampaign $model) {
                    return Html::a($model->title, Url::to(['/bid-manager/bids', 'YandexBidSearch[campaign_id]' => $model->id]));
                }
            ],
            [
                'attribute' => 'max_click_price',
                'value' => function (YandexCampaign $model) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'max_click_price-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'max_click_price',
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/index/ajax-update', 'id' => $model->primaryKey])
                        ],
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'strategy_1',
                'value' => function (YandexCampaign $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy-1-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'strategy_1',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/index/ajax-update', 'id' => $model->primaryKey])
                        ],
                        'displayValueConfig' => $strategies,
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'format' => 'raw',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'strategy_1',
                    $strategies,
                    ['class' => 'form-control', 'prompt' => 'Все']
                )
            ],
            [
                'attribute' => 'strategy_2',
                'value' => function (YandexCampaign $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy-2-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'strategy_2',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/index/ajax-update', 'id' => $model->primaryKey])
                        ],
                        'displayValueConfig' => $strategies,
                        'submitButton' => [
                            'class' => 'btn btn-sm btn-primary',
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>'
                        ]
                    ]);
                },
                'format' => 'raw',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'strategy_2',
                    $strategies,
                    ['class' => 'form-control', 'prompt' => 'Все']
                )
            ],

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
