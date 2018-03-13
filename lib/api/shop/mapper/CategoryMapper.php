<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 12:37
 */

namespace app\lib\api\shop\mapper;

use app\models\ExternalCategory;

/**
 * Маппер для категорий
 *
 * Class CategoryMapper
 * @package app\lib\api\shop\mapper
 */
class CategoryMapper extends BaseMapper
{
    /**
     * @param ExternalCategory $item
     * @return array
     */
    protected function prepareItem($item)
    {
        return [
            'id' => $item->primaryKey,
            'outer_id' => $item->outer_id,
            'title' => $item->title,
            'is_active' => true,
            'parent_id' => $item->parent_id
        ];
    }
}
