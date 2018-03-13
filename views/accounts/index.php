<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\AccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Аккаунты';
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

            'title',
            'units',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {update_token}',
                'buttons' => [
                    'update_token' => function ($url, \app\models\Account $model) {
                        if ($model->account_type == $model::ACCOUNT_TYPE_YANDEX) {
                            return Html::a(
                                'Обновить токен',
                                'https://oauth.yandex.ru/authorize?response_type=code&client_id=' .
                                    \app\helpers\ArrayHelper::getValue($model, 'account_data.yandex_application_id') .
                                    '&state=' . urlencode('account_id=' . $model->id)
                            );
                        }
                    }
                ]
            ],
        ],
    ]); ?>

</div>
