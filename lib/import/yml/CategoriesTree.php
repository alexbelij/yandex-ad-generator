<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 17:42
 */

namespace app\lib\import\yml;

use app\lib\import\yml\strategies\entity\Category;
use yii\helpers\ArrayHelper;

/**
 * Class CategoriesTree
 * @package app\lib\import\yml\strategies
 */
class CategoriesTree
{
    /**
     * @var Category[]
     */
    protected $items = [];

    /**
     * @var Category[]
     */
    protected $indexByParent = [];

    /**
     * CategoriesTree constructor.
     * @param Category[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->indexByParent[$item->parentId][] = $item;
            $this->items[$item->id] = $item;
        }
    }

    /**
     * @param int $categoryId
     * @param bool $all
     * @return Category|array
     */
    public function getChildren($categoryId, $all = false)
    {
        $children = isset($this->indexByParent[$categoryId]) ?
            $this->indexByParent[$categoryId] : [];

        if (empty($children)) {
            return [];
        }

        if ($all) {
            foreach ($children as $child) {
                $children = array_merge($children, $this->getChildren($child->id, $all));
            }
        }

        return $children;
    }

    /**
     * @param int $categoryId
     * @return Category|null
     */
    public function getParent($categoryId)
    {
        $category = ArrayHelper::getValue($this->items, $categoryId);
        if (!$category || !$category->parentId) {
            return null;
        }

        return ArrayHelper::getValue($this->items, $category->parentId);
    }

    /**
     * @param int $categoryId
     * @return Category[]
     */
    public function getParents($categoryId)
    {
        $category = ArrayHelper::getValue($this->items, $categoryId);
        $parents = [];
        if (!$category || !$category->parentId) {
            return $parents;
        }

        $parent = $this->getParent($categoryId);
        if ($parent) {
            $parents[] = $parent;
            $parents = array_merge($parents, $this->getParents($parent->id));
        }

        return $parents;
    }

    /**
     * @param int $categoryId
     * @return Category|null
     */
    public function getProductCategory($categoryId)
    {
        $category = ArrayHelper::getValue($this->items, $categoryId);
        if (!$category) {
            return null;
        }

        $parents = [$category];
        /** @var Category[] $parents */
        $parents = array_merge($parents, $this->getParents($categoryId));
        foreach ($parents as $parent) {
            //возможно это брэнд
            if (!isset($this->indexByParent[$parent->id])) {
                continue;
            } else {
                return $parent;
            }
        }

        return null;
    }

    /**
     * @param int $categoryId
     * @return Category|null
     */
    public function getBrand($categoryId)
    {
        $category = ArrayHelper::getValue($this->items, $categoryId);
        if (!$category) {
            return null;
        }

        $parents = [$category];
        /** @var Category[] $parents */
        $parents = array_merge($parents, $this->getParents($categoryId));
        foreach ($parents as $parent) {
            //возможно это брэнд
            if (!isset($this->indexByParent[$parent->id])) {
                return $parent;
            }
            continue;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }
}
