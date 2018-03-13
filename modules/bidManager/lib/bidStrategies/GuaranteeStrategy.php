<?php

namespace app\modules\bidManager\lib\bidStrategies;

/**
 * Class GuaranteeStrategy
 * @package app\modules\bidManager\lib\bidStrategies
 */
class GuaranteeStrategy extends BaseStrategy
{
    /**
     * @var string
     */
    protected $fieldPrefix = 'gar';

    /**
     * @var string
     */
    protected $patternRegexp = '#(\d)гар([+-]\d+)%#iu';
}
