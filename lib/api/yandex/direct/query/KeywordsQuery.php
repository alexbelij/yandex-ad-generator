<?php

namespace app\lib\api\yandex\direct\query;

use app\lib\api\yandex\direct\query\keywords\KeywordsSelectionCriteria;

/**
 * Class KeywordsQuery
 * @package app\lib\api\yandex\direct\query
 */
class KeywordsQuery extends AbstractQuery
{
    /**
     * @var array
     */
    public $fieldNames = [
        'Id', 'AdGroupId', 'CampaignId', 'Keyword', 'Bid',
        'ContextBid', 'StrategyPriority', 'Status', 'State',
        'Productivity', 'StatisticsSearch', 'StatisticsNetwork'
    ];

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return new KeywordsSelectionCriteria($params);
    }
}
