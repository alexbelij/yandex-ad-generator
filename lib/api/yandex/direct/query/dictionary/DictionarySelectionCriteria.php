<?php

namespace app\lib\api\yandex\direct\query\dictionary;

use app\lib\api\yandex\direct\query\selectionCriteria\SelectionCriteria;

/**
 * Class DictionarySelectionCriteria
 * @package app\lib\api\yandex\direct\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class DictionarySelectionCriteria extends SelectionCriteria
{
    /**
     * @var array
     */
    protected $dictionaryNames;

    /**
     * Выводит справочник регионов
     */
    public function searchGeoRegions()
    {
        $this->setDictionaryNames(['GeoRegions']);
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
        $this->dictionaryNames = $dictionaryNames;
    }
}
