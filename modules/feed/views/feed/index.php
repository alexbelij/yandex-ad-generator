<?php

use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedQueue;
use app\modules\feed\models\forms\UploadFeed;
use yii\web\View;
use yii\grid\GridView;

/**
 * @var View $this
 * @var UploadFeed $model
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

?>

<h3>Обработка фида - <?=$model->feed->title?></h3>


<?php

$form = \yii\bootstrap\ActiveForm::begin([
    'id' => 'upload-feed-form'
]);


echo $form->field($model, 'feedFile')->fileInput();

echo \yii\helpers\Html::submitButton('Загрузить', ['class' => 'btn btn-primary']);

$form->end();
?>

<div class="row">
    <div class="col-sm-12" style="margin-top: 20px;">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'original_filename',
                    'label' => 'Файл',
                ],
                [
                    'label' => 'Размер (Mb)',
                    'value' => function (FeedQueue $feedQueue) {
                        return round($feedQueue->size / 1024 / 1024, 2);
                    }
                ],
                'created_at',
                'finished_at',
                'status',
                'error_message',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                ],
            ],
        ]); ?>
    </div>
</div>