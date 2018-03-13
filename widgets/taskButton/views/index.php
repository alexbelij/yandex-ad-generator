<?php

use yii\helpers\Html;
use app\models\TaskQueue;
use yii\helpers\Inflector;

/**
 * @var TaskQueue $task
 */

$dataAttributes = [];

foreach ($attributes as $key => $value) {
    $key = str_replace('_', '-', Inflector::underscore($key));
    $dataAttributes['data-'.$key] = $value;
}

$buttonOptions = array_merge([
    'class' => 'btn btn-primary ' . $buttonClass,
], $dataAttributes);

?>

<?= Html::button($caption, $buttonOptions)?>
<?if (!empty($task)):?>
    <div>
        Последний запуск: <?=date('d.m.Y H:i:s', strtotime($task->completed_at))?>,
    </div>
    <div>
        статус: <span style="color: <?=!$hasError ? "green" :"red"?>"><?=$hasError ? "Ошибка" : "Успешно"?></span>,
        <?=Html::a('подробней', \yii\helpers\Url::to(['/task-queue/details', 'task_id' => $task->id, 'UpdateLogSearch[status]' => 'error']))?>
    </div>
<?endif?>
