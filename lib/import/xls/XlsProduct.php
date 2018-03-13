<?php

namespace app\lib\import\xls;

use yii\base\Object;

/**
 * Class XlsProduct
 * @package app\lib\import\xls
 */
class XlsProduct extends Object
{
    /**
     * @var string
     */
    public $model;

    /**
     * @var int
     */
    public $price;

    /**
     * @var string
     */
    public $category;

    /**
     * @var string
     */
    public $vendor;

    /**
     * @var string
     */
    public $url;
}
