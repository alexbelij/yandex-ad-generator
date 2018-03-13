<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 0:37
 */

namespace app\lib\api\shop\query\translator;

/**
 * Interface TranslatorInterface
 * @package app\lib\api\shop\query\translator
 */
interface TranslatorInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function translate(array $params);
}
