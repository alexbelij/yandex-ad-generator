<?php

namespace app\lib\services;

use app\lib\api\yandex\direct\resources\DictionaryResource;
use yii\caching\Cache;
use yii\caching\FileCache;
use yii\helpers\ArrayHelper;

/**
 * Сервис работы со словарями
 *
 * Class DictionaryService
 * @package app\lib\services
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class DictionaryService
{
    /**
     * @var DictionaryResource
     */
    protected $resource;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * DictionaryService constructor.
     * @param DictionaryResource $resource
     * @param Cache|null $cache
     */
    public function __construct(DictionaryResource $resource, Cache $cache = null)
    {
        $this->resource = $resource;
        if (is_null($cache)) {
            $this->cache = new FileCache();
        } else {
            $this->cache = $cache;
        }
    }

    /**
     * @return array|mixed
     */
    public function getGeoRegions()
    {
        $regions = $this->cache->get('yandex_regions');
        if (!$regions) {
            $regions = $this->resource->getGeoRegions();
            $this->cache->set('yandex_regions', $regions);
        }

        return $regions;
    }

    /**
     * Возвращает названия переданных регионов
     *
     * @param int|int[] $regionIds
     * @return array
     */
    public function getRegionTitles($regionIds)
    {
        $regionIds = array_map('intval', (array)$regionIds);
        $regions = ArrayHelper::index($this->getGeoRegions(), 'GeoRegionId');

        $result = [];
        foreach ($regionIds as $regionId) {
            if (isset($regions[$regionId])) {
                $result[] = $regions[$regionId]['GeoRegionName'];
            }
        }

        return $result;
    }
}
