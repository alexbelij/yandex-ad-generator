<?php

namespace app\modules\bidManager\lib\bidStrategies;

/**
 * Class SpecStrategy
 * @package app\modules\bidManager\lib\bidStrategies
 */
class SpecStrategy extends BaseStrategy
{
    /**
     * @var string
     */
    protected $fieldPrefix = 'spec';

    /**
     * @var string
     */
    protected $patternRegexp = '#(\d)СР([+-]\d+)%#iu';
}
