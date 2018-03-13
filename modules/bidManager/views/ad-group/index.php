<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\bidManager\models\search\YandexAdGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Yandex Ad Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-ad-group-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Yandex Ad Group', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'campaign_id',
            'account_id',
            'created_at',
            'updated_at',
            // 'name',
            // 'status',
            // 'type',
            // 'strategy_1',
            // 'strategy_2',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
