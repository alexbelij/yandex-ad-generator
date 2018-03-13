<?php

namespace app\lib\api\yandex\direct\resources;

/**
 * Class VCardsResource
 * @package app\lib\api\yandex\direct\resources
 */
class VCardsResource extends AbstractResource
{
    public $resourceName = 'VCards';

    public $queryClass = 'app\lib\api\yandex\direct\query\VcardsQuery';
}
