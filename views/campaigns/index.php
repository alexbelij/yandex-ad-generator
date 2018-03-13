<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\CampaignTemplate;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\YandexCampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список кампаний';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yandex-campaign-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'shop.name',
            'title',
            'yandex_id',
            [
                'label' => 'Шаблон кампании',
                'attribute' => 'campaign_template_id',
                'value' => function (\app\models\YandexCampaign $model) {
                    return $model->campaignTemplate->title;
                },
                'filter' => Html::activeDropDownList($searchModel, 'campaign_template_id', ArrayHelper::map(
                    CampaignTemplate::find()->all(), 'id', 'title'
                ), ['class' => 'form-control', 'prompt' => 'Все'])
            ],
            [
                'label' => 'Минус слова',
                'value' => function (\app\models\YandexCampaign $model) {
                    $words = \yii\helpers\ArrayHelper::getValue($model, 'yandexData.NegativeKeywords.Items', []);

                    return implode(', ', $words);
                }
            ],
            [
                'label' => 'Статус',
                'value' => function (\app\models\YandexCampaign $model) {
                    return \yii\helpers\ArrayHelper::getValue($model, 'yandexData.Status') . '(' .
                        \yii\helpers\ArrayHelper::getValue($model, 'yandexData.StatusClarification') . ')';
                }
            ],
            [
                'label' => 'Дневной бюджет',
                'value' => function (\app\models\YandexCampaign $model) {
                    return \yii\helpers\ArrayHelper::getValue($model, 'yandexData.DailyBudget.Amount') / 1000000;
                }
            ],
            [
                'label' => 'Валюта',
                'value' => function (\app\models\YandexCampaign $model) {
                    return \yii\helpers\ArrayHelper::getValue($model, 'yandexData.Currency');
                }
            ],
            'products_count',

            [
                'header' => 'Действия',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
