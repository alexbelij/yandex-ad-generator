<?php

use app\modules\feed\models\Feed;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\feed\models\search\FeedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Фиды';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feed-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новый фид', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'title',
                'value' => function (Feed $model) {
                    return Html::a($model->title, ['/feed/feed-items', 'FeedItemSearch[feed_id]' => $model->id]);
                },
                'format' => 'html'
            ],
            'domain',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {upload} {download}',
                'buttons' => [
                    'upload' => function ($url, Feed $model) {
                        $title = 'Загрузка фида';

                        return Html::a(
                            Html::tag('span', '', ['class' => 'glyphicon glyphicon-upload']),
                            Url::to(['/feed/feed', 'id' => $model->id]),
                            [
                                'title' => $title,
                                'aria-label' => $title,
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'download' => function ($url, Feed $model) {

                        return \yii\helpers\Html::a(
                            Html::tag('span', '', ['class' => 'glyphicon glyphicon-download']),
                            ['/feed/feed/download-feed', 'feedId' => $model->primaryKey],
                            [
                                'data-pjax' => '0',
                                'title' => 'Скачать',
                                'aria-label' => 'Скачать',
                            ]
                        );
                    }
                ]
            ],
        ],
    ]); ?>
</div>
