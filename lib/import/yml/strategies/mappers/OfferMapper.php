<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 17:31
 */

namespace app\lib\import\yml\strategies\mappers;

use app\helpers\ArrayHelper;
use app\lib\import\yml\strategies\entity\Offer;

/**
 * Class OfferMapper
 * @package app\lib\import\yml\strategies\mappers
 */
class OfferMapper extends AbstractMapper
{
    /**
     * @var array
     */
    protected $fields = [
        'url', 'currencyid', 'categoryid', 'price', 'picture', 'name', 'model',
        'description', 'delivery', 'local_delivery_cost', 'vendor', 'vendorcode',
        'market_category', 'dimensions', 'typeprefix', 'id', 'type', 'available'
    ];

    /**
     * @var array
     */
    private static $tagMap = [
        'available' => 'isAvailable',
        'currencyid' => 'currencyId',
        'categoryid' => 'categoryId',
        'vendorcode' => 'vendorCode',
        'local_delivery_cost' => 'localDeliveryCost',
        'market_category' => 'marketCategory',
        'typeprefix' => 'typePrefix'
    ];

    /**
     * @inheritDoc
     */
    public function map($data = [])
    {
        $data = array_merge($this->data, $data);
        $attrs = [];
        foreach ($this->fields as $field) {
            $sourceField = $targetField = $field;
            if (array_key_exists($field, self::$tagMap)) {
                $targetField = self::$tagMap[$field];
            }
            $attrs[$targetField] = ArrayHelper::getValue($data, $sourceField);
        }

        return new Offer($attrs);
    }

}
