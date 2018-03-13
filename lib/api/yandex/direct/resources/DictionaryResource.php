<?php

namespace app\lib\api\yandex\direct\resources;

use app\lib\api\yandex\direct\query\dictionary\DictionarySelectionCriteria;
use app\lib\api\yandex\direct\query\DictionaryQuery;
use app\lib\api\yandex\direct\query\Result;

/**
 * Получение данных из словаря
 *
 * Class DictionaryResource
 * @package app\lib\api\yandex\direct\resources
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class DictionaryResource extends AbstractResource
{
    public $resourceName = 'dictionaries';

    public $queryClass = 'app\lib\api\yandex\direct\query\DictionaryQuery';

    /**
     * @return array|mixed
     */
    public function getGeoRegions()
    {
        $query = new DictionaryQuery();
        $query->setDictionaryNames(DictionaryQuery::DICT_GEO_REGIONS);
        
        $result = $this->find($query);
        
        if (isset($result['GeoRegions'])) {
            return $result['GeoRegions'];
        } else {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    protected function createResult($result)
    {
        $result = $result['result'];

        $meta = [];
        if (isset($result['LimitedBy'])) {
            $meta['limitedBy'] = $result['LimitedBy'];
        }

        return new Result($result, $meta);
    }
}
