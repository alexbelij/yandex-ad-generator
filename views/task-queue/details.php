<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use app\models\YandexUpdateLog;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\search\UpdateLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Change log';

$this->params['breadcrumbs'][] = ['label' => 'Список задач', 'url' => ['/task-queue']];
$this->params['breadcrumbs'][] = 'Подробно';

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
            'entity_type',
            [
                'label' => 'Сущность',
                'attribute' => 'entity_id',
                'value' => function (\app\models\YandexUpdateLog $model) {
                    $value = $model->getEntityTitle();

                    $entityLabel = "$value (id - {$model->entity_id})";

                    if ($model->entity_type == YandexUpdateLog::ENTITY_AD && $model->ad) {
                        return Html::a(
                            $entityLabel,
                            Url::to(['generator/keywords',
                                'shopId' => $model->shop_id,
                                'ProductsSearch[id]' => $model->ad->ad->product_id,
                                'ProductsSearch[onlyActive]' => '0'
                            ]), [
                                'target' => '_blank'
                            ]
                        );
                    } elseif ($model->entity_type == YandexUpdateLog::ENTITY_AD) {
                        return $entityLabel . ' (удалена)';
                    }

                    return $entityLabel;
                },
                'format' => 'raw',
                'filterInputOptions' => [
                    'name' => $searchModel->formName() . '[entityTitle]',
                    'class' => 'form-control',
                    'value' => $searchModel->entityTitle
                ]
            ],
            [
                'label' => 'yandex id',
                'value' => function (YandexUpdateLog $model) {
                    if ($model->entity_type == YandexUpdateLog::ENTITY_AD) {
                        return ArrayHelper::getValue($model->ad, 'yandex_ad_id');
                    } else {
                        return ArrayHelper::getValue($model->campaign, 'yandex_id');
                    }
                }
            ],
            'created_at',
            'operation',
            'status',
            [
                'attribute' => 'message',
                'format' => 'html'
            ],
            'points'
        ],
    ]); ?>

</div>
