<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.04.16
 * Time: 14:59
 */

namespace app\lib\api\shop\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Class ApiProduct
 * Обертка над возвращаемыми апи товарами
 * @package app\lib\api\shop\models
 */
class ExtProduct extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $categories;

    /**
     * @var array
     */
    public $brand;

    /**
     * @var string
     */
    public $href;

    /**
     * @var string
     */
    public $seoTitle;

    /**
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $isAvailable;

    /**
     * @var float
     */
    public $price;

    /**
     * Расширенный заголовок
     * @var string
     */
    public $extTitle = '';

    /**
     * @var string
     */
    public $typePrefix;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $updatedAt;

    /**
     * ApiProduct constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $result = [];
        foreach ($config as $key => $value) {
            $key = Inflector::camelize($key);
            $key = strtolower(substr($key, 0, 1)) . substr($key, 1);
            $result[$key] = $value;
        }
        
        parent::__construct($result);
    }

    /**
     * @return int
     */
    public function getBrandId()
    {
        return (int)ArrayHelper::getValue($this->brand, 'id');
    }

    /**
     * @return string
     */
    public function getBrandTitle()
    {
        return ArrayHelper::getValue($this->brand, 'title');
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return ArrayHelper::getValue($this->categories, '0.title');
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return ArrayHelper::getValue($this->categories, '0.id');
    }

    /**
     * @param array $data
     * @return ExtProduct
     */
    public static function createFrom(array $data)
    {
        return new self([
            'categories' => ArrayHelper::getValue($data, 'categories'),
            'id' => ArrayHelper::getValue($data, 'id'),
            'brand' => ArrayHelper::getValue($data, 'brand'),
            'href' => ArrayHelper::getValue($data, 'href'),
            'image' => ArrayHelper::getValue($data, 'image'),
            'seo_title' => ArrayHelper::getValue($data, 'seo_title'),
            'title' => ArrayHelper::getValue($data, 'title'),
            'is_available' => ArrayHelper::getValue($data, 'is_available'),
            'price' => ArrayHelper::getValue($data, 'price'),
            'extTitle' => ArrayHelper::getValue($data, 'ext_title'),
            'typePrefix' => ArrayHelper::getValue($data, 'type_prefix'),
            'createdAt' => ArrayHelper::getValue($data, 'created_at'),
            'updatedAt' => ArrayHelper::getValue($data, 'updated_at'),
        ]);
    }

    /**
     * @param bool $rarelyServed
     * @return string
     */
    public function getShortHref($rarelyServed = false)
    {
        if (!$rarelyServed) {
            return $this->href;
        }

        return substr($this->href, 0, strpos($this->href, '/', 8) + 1) . '{param1}';
    }
}
