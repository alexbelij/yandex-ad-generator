<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\api\yandex\direct\query;

use app\lib\api\yandex\direct\query\vcards\VcardSelectionCriteria;

class VcardsQuery extends AbstractQuery
{
    public $fieldNames = [
        'Id', 'Country', 'City', 'Street', 'House', 'Building',
        'Apartment', 'CompanyName', 'ExtraMessage', 'ContactPerson',
        'CampaignId', 'Ogrn', 'WorkTime', 'Phone'
    ];

    /**
     * @inheritDoc
     */
    protected function createSelectionCriteria(array $params = [])
    {
        return new VcardSelectionCriteria($params);
    }

}
