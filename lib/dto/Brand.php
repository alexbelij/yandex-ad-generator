<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 06.11.16
 * Time: 9:03
 */

namespace app\lib\dto;

use yii\base\Object;

/**
 * Class Brand
 * @package app\lib\dto
 */
class Brand extends Object
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $points;
}
