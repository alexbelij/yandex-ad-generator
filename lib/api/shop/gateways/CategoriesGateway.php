<?php

namespace app\lib\api\shop\gateways;
use app\helpers\ArrayHelper;
use app\lib\api\shop\query\CategoryQuery;

/**
 * Class CategoriesGateway
 * @package app\lib\api\shop\gateways
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class CategoriesGateway extends BaseGateway
{
    /**
     * Возвращает список категорий
     *
     * @param array $ids
     * @param null $parentId
     * @return array
     */
    public function getList(array $ids = [], $parentId = null)
    {
        $query = new CategoryQuery();
        if (!empty($ids)) {
            $query->byIds($ids);
        }

        if (!empty($parentId)) {
            $query->byParentId($parentId);
        }

        $query->setOrder('title ASC');

        return $this->query($query);
    }
}
