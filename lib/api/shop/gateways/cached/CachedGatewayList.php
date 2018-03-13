<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.10.16
 * Time: 19:03
 */

namespace app\lib\api\shop\gateways\cached;

use app\lib\api\shop\gateways\BaseGateway;
use app\lib\api\shop\gateways\CategoriesGateway;
use yii\caching\Cache;
use yii\caching\FileCache;

/**
 * Class CategoriesService
 * @package app\lib\api\shop\services
 */
abstract class CachedGatewayList
{
    /**
     * @var CategoriesGateway
     */
    protected $gateway;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * CategoriesService constructor.
     * @param BaseGateway $gateway
     * @param Cache $cache
     */
    public function __construct(BaseGateway $gateway, Cache $cache = null)
    {
        $this->gateway = $gateway;

        if (!$cache) {
            $cache = new FileCache();
        }

        $this->cache = $cache;
    }

    /**
     * @return array|mixed
     */
    public function getList()
    {
        $cacheKey = $this->getCacheKey();
        $result = $this->cache->get($cacheKey);
        if (!$result) {
            $result = $this->getData();
            $this->cache->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Удаление кеша списка категорий
     */
    public function clearCacheList()
    {
        $this->cache->delete($this->getCacheKey());
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return get_class($this->gateway) . $this->gateway->getDataSource()->getShop()->id;
    }

    /**
     * @return mixed
     */
    abstract protected function getData();
}
