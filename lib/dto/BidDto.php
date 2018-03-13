<?php

namespace app\lib\dto;

use app\lib\api\yandex\direct\exceptions\YandexException;
use yii\base\Object;

/**
 * Class BidDto
 * @package app\lib\dto
 */
class BidDto extends Object
{
    const TYPE_KEYWORDS = 'keywords';
    const TYPE_CAMPAIGNS = 'campaigns';
    const TYPE_AD_GROUPS = 'adGroups';

    private static $fields = [
        self::TYPE_KEYWORDS => 'keywordId',
        self::TYPE_AD_GROUPS => 'adGroupId',
        self::TYPE_CAMPAIGNS => 'campaignId'
    ];

    /**
     * @var int
     */
    public $campaignId;

    /**
     * @var int
     */
    public $adGroupId;

    /**
     * @var int
     */
    public $keywordId;

    /**
     * @var int
     */
    public $bid;

    /**
     * @var int
     */
    public $contextBid;

    /**
     * @param string $type сущность, для которой необходимо обновить ставки
     * @return array
     */
    public function getQuery($type)
    {
        $field = self::getFieldByType($type);
        $value = $this->$field;

        $query = [
            ucfirst($field) => $value,
            'Bid' => $this->bid * 1000000,
            'ContextBid' => $this->contextBid
        ];

        return array_filter($query);
    }

    /**
     * @param string $type
     * @return string mixed
     * @throws YandexException
     */
    public static function getFieldByType($type)
    {
        if (!array_key_exists($type, self::$fields)) {
            throw new YandexException('Указан неправильный тип');
        }

        return self::$fields[$type];
    }

    /**
     * Метод создает массив однотипных объектов для обновления ставок.
     * Объекты отличаются только id сущностей
     *
     * @param int|int[] $ids
     * @param int $bid
     * @param string $type
     * @param array $params
     * @return BidDto[]
     */
    public static function createFor($ids, $bid, $type, $params = [])
    {
        $items = [];
        $field = self::getFieldByType($type);

        foreach ((array)$ids as $id) {

            $bidData = array_merge([
                $field => $id,
                'bid' => $bid
            ], $params);

            $items[] = new static($bidData);
        }

        return $items;
    }
}
