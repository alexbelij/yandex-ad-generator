<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 17:12
 */

namespace app\lib\import\yml\extensions;

/**
 * Interface ExtensionInterface
 * @package app\lib\import\yml\extensions
 */
interface ExtensionInterface
{
    /**
     * @param ExtensionItemDto $item
     * @return mixed
     */
    public function run(ExtensionItemDto $item);
}
