<?php

namespace app\lib\api\yandex\direct\query;


/**
 * Class DictionaryQuery
 * @package app\lib\api\yandex\direct\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class DictionaryQuery extends AbstractQuery
{
    const DICT_GEO_REGIONS = 'GeoRegions';

    /**
     * @var array
     */
    protected $dictionaryNames;

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setSelectionCriteria($selectionCriteria)
    {
        if (!empty($params['dictionaryNames'])) {
            $this->dictionaryNames = $params['dictionaryName'];
        }
    }

    /**
     * @return array
     */
    public function getDictionaryNames()
    {
        return $this->dictionaryNames;
    }

    /**
     * @param array $dictionaryNames
     */
    public function setDictionaryNames($dictionaryNames)
    {
        $this->dictionaryNames = (array)$dictionaryNames;
    }
}
