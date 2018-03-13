<?php

namespace app\lib\api\shop\query;

/**
 * Class CategoryQuery
 * @package app\lib\api\shop\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class CategoryQuery extends BaseQuery
{
    /**
     * @var array
     */
    protected $ids = [];

    /**
     * @var bool
     */
    protected $onlyActive = true;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param mixed $ids
     * @return $this
     */
    public function byIds($ids)
    {
        $this->ids = (array)$ids;
        return $this;
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function onlyActive($val = true)
    {
        $this->onlyActive = $val;
        return $this;
    }

    /**
     * @param mixed $parentId
     * @return $this
     */
    public function byParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function filterByTitle($name)
    {
        $this->title = $name;
        return $this;
    }
}
