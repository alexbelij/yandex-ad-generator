<?php

namespace app\lib\tasks;

use app\helpers\ArrayHelper;
use app\lib\services\MinusKeywordsService;
use app\models\AdKeyword;
use app\models\AdYandexGroup;
use app\models\search\ProductsSearch;
use yii\db\ActiveQuery;

/**
 * Минусация ключевых фраз
 *
 * Class MinusKeywordsTask
 * @package app\lib\tasks
 */
class MinusKeywordsTask extends AbstractTask
{
    const TASK_NAME = 'minusKeywords';

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();

        $productsSearch = new ProductsSearch();
        $dataProvider = $productsSearch->search([
            'shopId' => $this->task->shop_id,
            'categoryId' => ArrayHelper::getValue($context, 'categoryId'),
            'brandId' => ArrayHelper::getValue($context, 'brandId'),
            'dateFrom' => ArrayHelper::getValue($context, 'dateFrom'),
            'dateTo' => ArrayHelper::getValue($context, 'dateTo'),
            'title' => ArrayHelper::getValue($context, 'title'),
            'adTitle' => ArrayHelper::getValue($context, 'adTitle'),
            'isRequireVerification' => ArrayHelper::getValue($context, 'isRequireVerification'),
            'withoutAd' => false,
            'priceFrom' => ArrayHelper::getValue($context, 'priceFrom'),
            'priceTo' => ArrayHelper::getValue($context, 'priceTo'),
        ]);

        /** @var ActiveQuery $query */
        $query = clone $dataProvider->query;
        $query->select('ad.id');

        $query
            ->leftJoin(['ayc' => 'ad_yandex_campaign'], 'ayc.ad_id = ad.id')
            ->leftJoin(['ayg' => 'ad_yandex_group'], 'ayg.id = ayc.ad_yandex_group_id')
            ->andWhere([
                'OR',
                ['ayg.id' => null],
                ['!=', 'ayg.serving_status', AdYandexGroup::SERVING_STATUS_RARELY_SERVED]
            ]);

        $adKeywords = AdKeyword::find()
            ->joinWith(['ad.product'])
            ->andWhere(['product.shop_id' => $this->task->shop_id])
            ->andWhere(['ad_id' => $query])
            ->asArray()
            ->all();

        $minusKeywordsService = new MinusKeywordsService($this->getLogger());
        $result = $minusKeywordsService->execute(
            ArrayHelper::map($adKeywords, 'id', 'keyword')
        );

        foreach ($result as $id => $keyword) {
            AdKeyword::updateAll(['keyword' => $keyword], ['id' => $id]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'minus_keywords_' . $this->task->primaryKey;
    }
}
