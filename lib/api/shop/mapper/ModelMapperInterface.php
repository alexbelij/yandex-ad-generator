<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 12:30
 */

namespace app\lib\api\shop\mapper;

use yii\db\ActiveQuery;

/**
 * Interface QueryToCriteriaMapper
 * @package app\components\api\shop\mapper
 */
interface ModelMapperInterface
{
    /**
     * @param array $items
     * @return array
     */
    public function createResult(array $items);
}
