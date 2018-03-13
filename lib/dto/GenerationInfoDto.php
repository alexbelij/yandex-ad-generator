<?php

namespace app\lib\dto;

use yii\base\Object;

/**
 * Class GenerationInfoDto
 * @package app\lib\dto
 */
class GenerationInfoDto extends Object
{
    /**
     * @var string
     */
    public $brandTitle;

    /**
     * @var array
     */
    public $categories = [];

    /**
     * @var string
     */
    public $productTitle;

    /**
     * @var array
     */
    public $titleVariants = [];

    /**
     * @var array
     */
    public $rotationTitles = [];
}
