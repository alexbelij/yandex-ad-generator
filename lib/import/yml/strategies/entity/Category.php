<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 15:12
 */

namespace app\lib\import\yml\strategies\entity;

use yii\base\Object;

/**
 * Сущность категории
 *
 * Class Category
 * @package app\lib\import\yml\strategies\entity
 */
class Category extends Object
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $parentId;

    /**
     * @var string
     */
    public $title;
}
