<?php

namespace app\lib\api\yandex\direct\query\selectionCriteria;

/**
 * Class BidSelectionCriteria
 * @package app\lib\api\yandex\direct\query\selectionCriteria
 */
class BidSelectionCriteria extends SelectionCriteria
{
    /**
     * @var int[]
     */
    public $keywordIds;

    /**
     * @var int[]
     */
    public $adGroupIds;

    /**
     * @var int[]
     */
    public $campaignIds;

    /**
     * @var string[]
     */
    public $servingStatuses;
}
