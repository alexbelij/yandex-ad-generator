<?php

namespace app\lib\api\yandex\direct\query\selectionCriteria;

use yii\base\Object;

/**
 * Class SelectionCriteria
 * @package app\lib\api\yandex\direct\query\selectionCriteria
 */
class SelectionCriteria extends Object implements CriteriaInterface
{
    /**
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data)
    {
        return new static($data);
    }

    /**
     * @inheritDoc
     */
    public function getCriteria()
    {
        $objectVars = array_filter(get_object_vars($this));

        $criteria = [];
        foreach ($objectVars as $field => $value) {
            $criteria[ucfirst($field)] = $value;
        }

        return $criteria;
    }
}
