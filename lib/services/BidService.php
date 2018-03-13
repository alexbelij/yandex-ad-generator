<?php

namespace app\lib\services;

use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\resources\BidResource;
use app\lib\dto\BidDto;
use yii\base\Exception;

/**
 * Class BidService
 * @package app\lib\services
 */
class BidService extends YandexService
{
    const LIMITS = [
        BidDto::TYPE_KEYWORDS => 10000,
        BidDto::TYPE_CAMPAIGNS => 10,
        BidDto::TYPE_AD_GROUPS => 1000
    ];

    /**
     * @var BidResource
     */
    protected $resource;

    /**
     * BidService constructor.
     * @param BidResource $resource
     */
    public function __construct(BidResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * За один выхов можно обновить только один тип ставок
     *
     * @param BidDto|BidDto[] $bidDto
     * @param string $type
     * @return ChangeResult
     * @throws Exception
     */
    public function updateBids($bidDto, $type)
    {
        if (!array_key_exists($type, self::LIMITS)) {
            throw new Exception('Передан неизвестный тип для обновления ставок');
        }

        $bidDtos = is_array($bidDto) ? $bidDto : [$bidDto];
        $limit = self::LIMITS[$type];
        $offset = 0;

        $result = new ChangeResult();

        /** @var BidDto[] $bids */
        while ($bids = array_slice($bidDtos, $offset, $limit)) {
            $offset += $limit;
            $setQuery = [];
            foreach ($bids as $bid) {
                $setQuery[] = $bid->getQuery($type);
            }

            $result->merge(
                $this->resource->set($setQuery)
            );
        }

        return $result;
    }
}
