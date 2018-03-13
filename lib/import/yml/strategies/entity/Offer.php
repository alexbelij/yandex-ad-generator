<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 17:15
 */

namespace app\lib\import\yml\strategies\entity;

use yii\base\Object;

/**
 * Class Offer
 * @package app\lib\import\yml\strategies\entity
 */
class Offer extends Object
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $isAvailable;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $typePrefix;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $currencyId;

    /**
     * @var string
     */
    public $categoryId;

    /**
     * @var string
     */
    public $picture;

    /**
     * @var bool
     */
    public $delivery;

    /**
     * @var float
     */
    public $localDeliveryCost;

    /**
     * @var string
     */
    public $vendor;

    /**
     * @var string
     */
    public $vendorCode;

    /**
     * @var string
     */
    public $marketCategory;

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $dimensions;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $salesNotes;

    /**
     * @var string
     */
    public $countryOfOrigin;
}
