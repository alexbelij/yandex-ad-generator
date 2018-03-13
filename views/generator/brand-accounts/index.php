<?php

use app\models\search\BrandAccountSearch;
use yii\data\DataProviderInterface;
use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var BrandAccountSearch $searchModel
 * @var DataProviderInterface $dataProvider
 * @var \yii\web\View $this
 * @var \app\lib\services\BrandCountService $brandCountService
 */

$this->title = 'Бренды и аккаунты';

?>

<div class="brand-accounts">
    <h1><?= Html::encode($this->title)?></h1>

    <div class="col-sm-12">

        <?= GridView::widget([
            'id' => 'brand-accounts-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => \yii\grid\SerialColumn::className()],
                [
                    'label' => 'Бренд',
                    'value' => function (\app\models\BrandAccount $model) use ($brandCountService) {
                        return $model->brandTitle . ' (' . $brandCountService->getCount($model->brand_id) . '/' . $brandCountService->getCountByFilter($model->brand_id) . ')';
                    }
                ],
                [
                    'label' => 'Аккаунт',
                    'value' => function (\app\models\BrandAccount $model) {
                        if ($model->account) {
                            return Html::a($model->account->title, \yii\helpers\Url::to(['/accounts/view', 'id' => $model->account_id]));
                        }

                        return null;
                    },
                    'format' => 'html'
                ],
                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'template' => '{update}',
                    'buttons' => [
                        'update' => function ($url, \app\models\BrandAccount $model, $key) {
                            $options = array_merge([
                                'title' => Yii::t('yii', 'Update'),
                                'aria-label' => Yii::t('yii', 'Update'),
                                'data-pjax' => '0',
                            ]);

                            $url = \yii\helpers\Url::to(['/generator/brand-accounts/update', 'shopId' => $model->shop_id, 'brandId' => $model->brand_id]);

                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options);
                        }
                    ]
                ]
            ]
        ])?>

    </div>

</div>
