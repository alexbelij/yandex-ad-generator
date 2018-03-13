<?php

use app\modules\feed\models\QuickRedirect;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\feed\models\search\QuickRedicrectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Редирект для быстрых ссылок';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="quick-redirect-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Создать новый редирект', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'source',
                'format' => 'raw',
                'value' => function (QuickRedirect $model) {
                    $url = rtrim(Yii::$app->request->getHostInfo(), '/') . '/q/' . $model->source;
                    return Html::a($url, $url, ['data-pjax' => '0']);
                }
            ],
            'target',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>
</div>
