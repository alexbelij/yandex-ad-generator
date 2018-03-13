<?php

namespace app\models\forms;

use app\helpers\ArrayHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\models\AdTemplate;
use app\models\AdTemplateBrand;
use app\models\AdTemplateCategory;
use app\models\ExternalBrand;
use yii\base\Exception;

/**
 * Class AdTemplateForm
 * @package app\models\forms
 */
class AdTemplateForm extends AdTemplate
{
    /**
     * @var array
     */
    public $brandIds;

    /**
     * @var array
     */
    public $categoryIds;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['brandIds', 'categoryIds'], 'safe']
        ]);
    }

    /**
     * @return ExternalBrand[]
     */
    public function getBrandsList()
    {
        /** @var BrandsGateway $brandsApiGateway */
        $brandsApiGateway = BrandsGateway::factory($this->shop);

        return $brandsApiGateway->getBrandsList();
    }

    /**
     * @return array
     */
    public function getSelectedBrandIds()
    {
        if (is_null($this->brandIds)) {
            return $this->getBrandIds();
        } else {
            return $this->brandIds;
        }
    }

    /**
     * @return array|mixed
     */
    public function getCategoriesTree()
    {
        /** @var CategoriesGateway $categoriesGateway */
        $categoriesGateway = CategoriesGateway::factory($this->shop);
        $categories = $categoriesGateway->getList();

        $result = [];
        if (is_null($this->categoryIds)) {
            $selectedCategoriesIds = $this->getCategoryIds();
        } else {
            $selectedCategoriesIds = $this->categoryIds;
        }

        $categoryIds = ArrayHelper::getColumn($categories, 'id');

        foreach ($categories as $category) {

            if (!in_array($category['parent_id'], $categoryIds)) {
                $category['parent_id'] = null;
            }

            $result[] = [
                'id' => (string)$category['id'],
                'text' => $category['title'],
                'parent' => $category['parent_id'] ? (string) $category['parent_id'] : '-1',
                'state' => [
                    'selected' => is_array($selectedCategoriesIds) && in_array($category['id'], $selectedCategoriesIds)
                ]
            ];
        }

        array_unshift($result, [
            'id' => '-1',
            'text' => 'Все',
            'parent' => '#',
            'state' => [
                'selected' => $this->isNew()
            ]
        ]);

        return $result;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->brandIds) && empty($this->categoryIds)) {
            $this->addError('brandIds', 'Необходимо выбрать бренды или категории');
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if (!parent::save($runValidation, $attributeNames)) {
                $transaction->rollBack();
                return false;
            }

            $this->updateCategories();
            $this->updateBrands();

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * Обновление категорий
     *
     * @throws Exception
     */
    protected function updateCategories()
    {
        $existsCategoriesIds = $this->getCategoryIds();
        $categoryIds = $this->categoryIds ?: [];
        $toDeleteCategoriesIds = array_diff($existsCategoriesIds, $categoryIds);
        $toAddCategoriesIds = array_diff($categoryIds, $existsCategoriesIds);

        if (!empty($toDeleteCategoriesIds)) {
            AdTemplateCategory::deleteAll(
                ['and', ['ad_template_id' => $this->primaryKey], ['category_id' => $toDeleteCategoriesIds]]
            );
        }

        if (!empty($toAddCategoriesIds)) {
            foreach ($toAddCategoriesIds as $categoriesId) {
                $adTemplateCategory = new AdTemplateCategory([
                    'ad_template_id' => $this->primaryKey,
                    'category_id' => $categoriesId
                ]);
                if (!$adTemplateCategory->save()) {
                    throw new Exception(ArrayHelper::first($adTemplateCategory->getFirstErrors()));
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function updateBrands()
    {
        $existsBrandIds = $this->getBrandIds();
        $brandIds = $this->brandIds ?: [];
        $toDeleteBrandIds = array_diff($existsBrandIds, $brandIds);
        $toAddBrandIds = array_diff($brandIds, $existsBrandIds);

        if (!empty($toDeleteBrandIds)) {
            AdTemplateBrand::deleteAll(
                ['and', ['ad_template_id' => $this->primaryKey], ['brand_id' => $toDeleteBrandIds]]
            );
        }

        if (!empty($toAddBrandIds)) {
            foreach ($toAddBrandIds as $categoriesId) {
                $adTemplateBrand = new AdTemplateBrand([
                    'ad_template_id' => $this->primaryKey,
                    'brand_id' => $categoriesId
                ]);
                if (!$adTemplateBrand->save()) {
                    throw new Exception(ArrayHelper::first($adTemplateBrand->getFirstErrors()));
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNewRecord && is_null($this->brandIds) && is_null($this->categoryIds);
    }
}
