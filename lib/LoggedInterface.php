<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.04.16
 * Time: 19:51
 */

namespace app\lib;

interface LoggedInterface
{
    /**
     * @return string
     */
    public function getEntityType();

    /**
     * @return int
     */
    public function getEntityId();
}
