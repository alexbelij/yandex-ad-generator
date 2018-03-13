<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 16:11
 */

namespace app\lib\api\shop\mapper;

use app\models\BaseModel;
use app\models\Shop;
use yii\base\Exception;
use yii\db\ActiveQuery;

/**
 * Class BaseMapper
 * @package app\lib\api\shop\mapper
 */
class BaseMapper implements ModelMapperInterface
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * BaseMapper constructor.
     * @param Shop $shop
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @inheritDoc
     */
    public function createResult(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->prepareItem($item);
        }

        return $result;
    }

    /**
     * @param BaseModel $item
     * @throws Exception
     */
    protected function prepareItem($item)
    {
        throw new Exception('Не реализовано');
    }
}
