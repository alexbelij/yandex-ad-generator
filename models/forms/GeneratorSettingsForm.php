<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 10.04.16
 * Time: 10:10
 */

namespace app\models\forms;

use app\helpers\TreeHelper;
use app\lib\api\shop\gateways\cached\CachedGatewayList;
use app\models\GeneratorSettings;
use yii\caching\Cache;
use yii\caching\FileCache;
use yii\helpers\ArrayHelper;

/**
 * Форма настройки генератора объявлений
 *
 * Class GeneratorSettingsForm
 * @package app\models\forms
 */
class GeneratorSettingsForm extends GeneratorSettings
{
    /**
     * @var array
     */
    private $availableBrands;

    /**
     * @var array
     */
    private $availableCategories;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['brandsList', 'safe']
        ]);
    }

    /**
     * Вовзращает список брендов в виде массива
     * @return mixed
     */
    public function getBrandsList()
    {
        return explode(',', $this->brands);
    }

    /**
     * Сохраняет список брендов
     *
     * @param array $brands
     */
    public function setBrandsList($brands)
    {
        $this->brands = implode(',', (array)$brands);
    }

    /**
     * @param array $brands
     * @return $this
     */
    public function setAvailableBrands(array $brands)
    {
        $this->availableBrands = $brands;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableBrandsList()
    {
        return $this->availableBrands;
    }

    /**
     * @return array
     */
    public function getAvailableCategories()
    {
        return $this->availableCategories;
    }

    /**
     * @param array $availableCategories
     * @return GeneratorSettingsForm
     */
    public function setAvailableCategories($availableCategories)
    {
        $this->availableCategories = $availableCategories;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableCategoriesList()
    {
        return $this->availableCategories;
    }

    /**
     * @return array
     */
    public function getCategoriesForTree()
    {
        return TreeHelper::getCategoriesTree($this->getAvailableCategoriesList(), $this->getCategoryIds());
    }
}
