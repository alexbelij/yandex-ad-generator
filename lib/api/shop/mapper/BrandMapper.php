<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 12:32
 */

namespace app\lib\api\shop\mapper;

use app\models\ExternalBrand;
use yii\db\ActiveQuery;

/**
 * Class BrandQueryMapper
 * @package app\components\api\shop\mapper
 */
class BrandMapper extends BaseMapper
{
    /**
     * @param ExternalBrand $item
     * @return array
     */
    protected function prepareItem($item)
    {
        return [
            'id' => $item->primaryKey,
            'title' => $item->title,
            'is_active' => true
        ];
    }
}
