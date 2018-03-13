<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.04.16
 * Time: 23:17
 */

namespace app\lib\api\yandex\direct\query;

use app\lib\api\yandex\direct\query\sitelinks\SitelinksSelectionCriteria;

class SitelinksQuery extends AbstractQuery
{
    public $fieldNames = [
        'Id', 'Sitelinks'
    ];

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return new SitelinksSelectionCriteria($params);
    }

}