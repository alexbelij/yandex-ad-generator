<?php

use app\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $task \app\models\TaskQueue */

$context = $task->getContext();

$errorUrl = Url::to(['/task-queue/details', 'task_id' => $task->id, 'UpdateLogSearch[status]' => 'error']);
$detailsUrl = Url::to(['/task-queue/details', 'task_id' => $task->id]);
$withoutAdUrl = Url::to([
    '/generator/keywords',
    'shopId' => $task->shop_id,
    'ProductsSearch[brandId]' => ArrayHelper::getValue($context, 'brandIds'),
    'ProductsSearch[withoutAd]' => 1,
    'ProductsSearch[categoryId]' => ArrayHelper::getValue($context, 'categoryIds')
])

?>
<p>Последний запуск: <?=$task->started_at?>, Статус: <?=$task->getStatusLabel()?>.</p>
<p>Подробней <?=Html::a($errorUrl, $errorUrl)?></p>
<p>Подробно: <?=Html::a($detailsUrl, $detailsUrl)?></p>
<p>Количество товаров без заголовков: <?=$withoutTitleCount?></p>
<p>Подробно: <?=Html::a($withoutAdUrl, $withoutAdUrl)?></p>