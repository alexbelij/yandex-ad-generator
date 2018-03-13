<?php

namespace app\lib\api\yandex\direct\resources;

/**
 * Class AdGroupResource
 * @package app\lib\api\yandex\direct\resources
 */
class AdGroupResource extends AbstractResource
{
    protected $resourceName = 'AdGroups';
    
    protected $queryClass = 'app\lib\api\yandex\direct\query\AdGroupQuery';
}
