<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\FileImportLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Подробности импорта файла - ' . $searchModel->fileImport->original_filename;
$this->params['breadcrumbs'][] = ['label' => 'Назад', 'url' => ['/task-queue']];

$additionalInfo = [];

if (!empty($searchModel->fileImport->company_name)) {
    $additionalInfo[] = "кампания: {$searchModel->fileImport->company_name}";
}

if (!empty($searchModel->fileImport->catalog_date)) {
    $additionalInfo[] = "файл сгенерирован: {$searchModel->fileImport->catalog_date}";
}

?>
<div class="file-import-log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <h4><?=Html::encode(implode(', ', $additionalInfo))?></h4>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'operation',
            'old_value:ntext',
             'new_value:ntext',
             'entity_type',
             'entity_id',
             'created_at',
        ],
    ]); ?>

</div>
