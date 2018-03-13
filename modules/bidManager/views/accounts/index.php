<?php

use app\helpers\ArrayHelper;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\Strategy;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\bidManager\models\search\AccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список аккаунтов';
$this->params['breadcrumbs'][] = $this->title;

$strategies = ArrayHelper::map(Strategy::find()->all(), 'id', 'strategy');
array_unshift($strategies, 'не задано');

?>
<div class="account-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новый аккаунт', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'last_updated_at',
            [
                'attribute' => 'max_click_price',
                'value' => function (Account $model) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'max_click_price-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'max_click_price',
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/accounts/ajax-update', 'id' => $model->primaryKey])
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
                'value' => function (Account $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy-1-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'strategy_1',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/accounts/ajax-update', 'id' => $model->primaryKey])
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
                'value' => function (Account $model) use ($strategies) {
                    return \kartik\editable\Editable::widget([
                        'options' => [
                            'id' => 'strategy-2-' . $model->id,
                        ],
                        'model' => $model,
                        'attribute' => 'strategy_2',
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'data' => $strategies,
                        'ajaxSettings' => [
                            'url' => Url::to(['/bid-manager/accounts/ajax-update', 'id' => $model->primaryKey])
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
            'units',
            [
                'label' => '',
                'value' => function (Account $model) {
                    $content = Html::a('Действия<b class="caret"></b>', '#', [
                            'class' => 'dropdown-toggle',
                            'data-toggle' => 'dropdown'
                        ]) . \yii\bootstrap\Dropdown::widget([
                            'items' => [
                                [
                                    'label' => 'Обновить токен',
                                    'url' => 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' .
                                        \app\helpers\ArrayHelper::getValue($model, 'settings.yandex_application_id') .
                                        '&state=' . urlencode('bid_account_id=' . $model->id)
                                ],
                                [
                                    'label' => 'Обновление кампаний',
                                    'url' => \yii\helpers\Url::to(['/bid-manager/sync/campaign', 'id' => $model->id]),
                                    'options' => [
                                        'class' => 'sync',
                                        'data-message' => 'Обновить список кампаний?'
                                    ]
                                ],
                                [
                                    'label' => 'Полное обновление',
                                    'url' => \yii\helpers\Url::to(['/bid-manager/sync/full', 'id' => $model->id]),
                                    'options' => [
                                        'class' => 'sync',
                                        'data-message' => 'Выполнить полное обновление?'
                                    ]
                                ],
                                [
                                    'label' => 'Дельта обновление',
                                    'url' => \yii\helpers\Url::to(['/bid-manager/sync/delta', 'id' => $model->id]),
                                    'options' => [
                                        'class' => 'sync',
                                        'data-message' => 'Выполнить дельта обновление?'
                                    ]
                                ],
                                [
                                    'label' => 'Синхронизация ставок',
                                    'url' => \yii\helpers\Url::to(['/bid-manager/sync/bid-update', 'id' => $model->id]),
                                    'options' => [
                                        'class' => 'sync',
                                        'data-message' => 'Выполнить синхронизацию?'
                                    ]
                                ],
                            ]
                        ]);

                    return Html::tag('div', $content, ['class' => 'dropdown']);
                },
                'format' => 'raw'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
            ],
        ],
    ]); ?>
</div>


<?php


$this->registerJs('
$(".sync").click(function(e) {
    e.preventDefault();
    
    var $this = $(this);
    
    bootbox.confirm({
        message: $this.data("message"),
        callback: function (res) {
            if (res) {
                $.ajax({
                    url: $this.find("a").attr("href"),
                    method: "POST",
                    success: function (result) {
                        bootbox.alert("Успех");
                    }
                });
            }
        }
    });
});
');