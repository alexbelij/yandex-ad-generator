<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 20:20
 */

namespace app\lib\import\yml\extensions;

use yii\base\Object;

/**
 * Class ExtensionItem
 * @package app\lib\import\yml\extensions
 */
class ExtensionItemDto extends Object
{
    /**
     * @var mixed
     */
    public $data;

    /**
     * @var array
     */
    public $extra = [];
}
