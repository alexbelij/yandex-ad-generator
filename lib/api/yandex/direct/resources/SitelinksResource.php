<?php

namespace app\lib\api\yandex\direct\resources;

/**
 * Class SitelinksResource
 * @package app\lib\api\yandex\direct\resources
 */
class SitelinksResource extends AbstractResource
{
    public $resourceName = 'Sitelinks';

    public $queryClass = 'app\lib\api\yandex\direct\query\SitelinksQuery';

    public $baseStructure = 'SitelinksSets';
}
