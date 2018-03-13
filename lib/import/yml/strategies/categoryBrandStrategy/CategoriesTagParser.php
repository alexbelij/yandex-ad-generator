<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 14:56
 */

namespace app\lib\import\yml\strategies\categoryBrandStrategy;

use app\helpers\ArrayHelper;
use app\lib\import\yml\AbstractTagParser;
use app\lib\import\yml\strategies\entity\Category;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use yii\console\Exception;

/**
 * Class CategoriesTagParser
 * @package app\lib\import\yml\strategies\categoryBrandStrategy
 */
class CategoriesTagParser extends AbstractTagParser
{
    /**
     * @var Category[]
     */
    protected $categories = [];

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @inheritDoc
     */
    public function parseAttributes($tagName, $attributes)
    {
        // TODO: Implement parseAttributes() method.
    }

    /**
     * @inheritDoc
     */
    public function parseCharacters($tagName, $data)
    {
        // TODO: Implement parseCharacters() method.
    }

    /**
     * @inheritDoc
     */
    public function end($tagName)
    {
        /** @var Category[] $brands */
        $brands = [];

        foreach ($this->categories as $item) {
            if (!$this->isParent($item)) {
                $brands[] = $item;
            }
        }

        foreach ($this->categories as $category) {
            /** @var ExternalCategory $categoryModel */
            $categoryModel = ExternalCategory::find()
                ->andWhere([
                    'shop_id' => $this->fileImport->shop_id,
                    'outer_id' => $category->id
                ])->one();

            if (!$categoryModel) {
                $categoryModel = new ExternalCategory([
                    'outer_id' => $category->id,
                    'title' => $category->title,
                    'parent_id' => $category->parentId,
                    'shop_id' => $this->fileImport->shop_id,
                    'original_title' => $category->title,
                ]);
                $categoryModel->save();
            } elseif (!$categoryModel->is_manual) {
                $categoryModel->title = $category->title;
                $categoryModel->original_title = $category->title;
                $categoryModel->parent_id = $category->parentId;
                $categoryModel->save();
            }
        }

        foreach ($brands as $brand) {
            /** @var ExternalBrand $brandModel */
            $brandModel = ExternalBrand::find()
                ->andWhere([
                    'shop_id' => $this->fileImport->shop_id,
                    'title' => $brand->title
                ])
                ->one();

            if (!$brandModel) {
                $brandModel = new ExternalBrand([
                    'outer_id' => $brand->id,
                    'title' => $brand->title,
                    'shop_id' => $this->fileImport->shop_id,
                    'original_title' => $brand->title
                ]);
                if (!$brandModel->save()) {
                    throw new Exception(ArrayHelper::first($brandModel->getFirstErrors()));
                }
            } elseif (!$brandModel->is_manual) {
                $brandModel->title = $brand->title;
                $brandModel->outer_id = $brand->id;
                $brandModel->original_title = $brand->title;
                if (!$brandModel->save()) {
                    throw new Exception(ArrayHelper::first($brandModel->getFirstErrors()));
                }
            }
        }
    }

    /**
     * Является ли категория родителем
     *
     * @param Category $category
     * @return bool
     */
    protected function isParent(Category $category)
    {
        foreach ($this->categories as $item) {
            if ($category->id == $item->parentId) {
                return true;
            }
        }

        return false;
    }
}
