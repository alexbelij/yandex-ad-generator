<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.03.16
 * Time: 22:06
 */

namespace app\lib\api\shop\query;

class BrandQuery extends BaseQuery
{
    /**
     * @var
     */
    protected $ids;

    /**
     * Выводить только активные
     * @var bool
     */
    protected $onlyActive = true;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param mixed $id
     * @return $this
     */
    public function byIds($id)
    {
        $this->ids = $id;
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
     * @param string $name
     * @return $this
     */
    public function filterByTitle($name)
    {
        $this->title = $name;
        return $this;
    }
}
