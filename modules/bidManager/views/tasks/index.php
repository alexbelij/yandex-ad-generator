<?php

use app\modules\bidManager\models\Task;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\bidManager\models\search\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Задачи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'label' => 'Задача',
                'attribute' => 'taskLabel',
                'format' => 'html',
                'value' => function (Task $model) {
                    return Html::a($model->getTaskLabel(), \yii\helpers\Url::to(['/bid-manager/task-logs', 'TaskLogSearch[task_id]' => $model->primaryKey]));
                }
            ],
            'created_at',
            'started_at',
            'finished_at',
            'account.title',
            'status',
            'total_points',
            'message:ntext',
            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{delete}'
            ]
        ],
    ]); ?>
</div>
