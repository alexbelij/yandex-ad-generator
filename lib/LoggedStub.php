<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.04.16
 * Time: 20:24
 */

namespace app\lib;

use yii\base\Object;

class LoggedStub extends Object implements LoggedInterface
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $id;

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->id;
    }
}
