<?php

namespace app\lib\api\yandex\direct\query;

use app\lib\api\yandex\direct\query\selectionCriteria\BidSelectionCriteria;

/**
 * Class BidQuery
 * @package app\lib\api\yandex\direct\query
 */
class BidQuery extends AbstractQuery
{
    /**
     * @var array
     */
    protected $fieldNames = [
        'KeywordId', 'AdGroupId', 'CampaignId', 'ServingStatus', 'Bid', 'ContextBid', 'StrategyPriority',
        'CompetitorsBids', 'SearchPrices', 'ContextCoverage', 'MinSearchPrice',
        'CurrentSearchPrice', 'AuctionBids'
    ];

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return new BidSelectionCriteria($params);
    }

}
