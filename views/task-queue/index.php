<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TaskQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tasks';
?>
<div class="task-queue-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'shop.name'
            ],
            'created_at',
            'started_at',
            'completed_at',
            'operation',
            [
                'label' => 'Статус',
                'value' => function (\app\models\TaskQueue $model) {
                    return $model->getStatusLabel();
                }
            ],
            [
                'label' => 'Списано баллов',
                'value' => function (\app\models\TaskQueue $model) {
                    return $model->getTotalPoints();
                }
            ],
            'info',
            'error',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete} {log}',
                'contentOptions'=>['style'=>'min-width: 80px;'],
                'buttons' => [
                    'view' => function ($url, \app\models\TaskQueue $model, $key) {

                        if ($model->operation == \app\lib\tasks\ImportFileTask::TASK_NAME) {
                            return null;
                        }

                        $url = Url::to(['/task-queue/details', 'task_id' => $model->id]);

                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>',
                            $url,
                            [
                                'title' => 'Подробно',
                                'aria-label' => 'Подробно',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'log' => function ($url, \app\models\TaskQueue $model, $key) {

                        if (!$model->log_file) {
                            return '';
                        }

                        return Html::a(
                            Html::tag('i', '', ['class' => 'glyphicon glyphicon-download-alt']),
                            Url::to(['/task-queue/download-log', 'taskId' => $model->primaryKey]),
                            [
                                'title' => 'Скачать лог',
                                'data-pjax' => '0'
                            ]
                        );
                    }
                ]
            ],

        ],
    ]); ?>

</div>
