<?php

namespace app\lib\api\yandex\direct\resources;

/**
 * Class KeywordsResource
 * @package app\lib\api\yandex\direct\resources
 */
class KeywordsResource extends AbstractResource
{
    protected $resourceName = 'Keywords';
    
    protected $queryClass = 'app\lib\api\yandex\direct\query\KeywordsQuery';
}
