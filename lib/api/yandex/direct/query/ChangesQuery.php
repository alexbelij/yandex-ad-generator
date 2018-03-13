<?php

namespace app\lib\api\yandex\direct\query;

use app\lib\api\yandex\direct\query\selectionCriteria\ChangesCriteria;

/**
 * Class ChangesQuery
 * @package app\lib\api\yandex\direct\query
 */
class ChangesQuery extends AbstractQuery
{
    /**
     * @var int[]
     */
    public $campaignIds;

    /**
     * @var int[]
     */
    public $adGroupIds;

    /**
     * @var int[]
     */
    public $adIds;

    /**
     * @var string
     */
    public $timestamp;

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return null;
    }

}
