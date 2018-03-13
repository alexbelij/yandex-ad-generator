<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\services;

use app\lib\api\yandex\direct\resources\VCardsResource;
use app\models\Vcard;
use app\models\YandexCampaign;

/**
 * Class VcardsService
 * @package app\lib\services
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class VcardsService extends YandexService
{
    /**
     * @var VCardsResource
     */
    protected $vCardResource;

    /**
     * VcardsService constructor.
     * @param VCardsResource $vCardResource
     */
    public function __construct(VCardsResource $vCardResource)
    {
        $this->vCardResource = $vCardResource;
    }

    /**
     * Создание новой визитки
     *
     * @param YandexCampaign $campaign
     * @param Vcard $vcard
     * @return mixed
     * @throws \app\lib\api\yandex\direct\exceptions\YandexException
     */
    public function createCardFor(YandexCampaign $campaign, Vcard $vcard)
    {
        $data = [
            'CampaignId' => $campaign->yandex_id,
            'Country' => $vcard->country,
            'City' => $vcard->city,
            'CompanyName' => $vcard->company_name,
            'WorkTime' => $vcard->work_time,
            'Phone' => array_filter([
                'CountryCode' => $vcard->phone_country_code,
                'CityCode' => $vcard->phone_city_code,
                'PhoneNumber' => $vcard->phone_number,
                'Extension' => $vcard->phone_extension
            ]),
            'Street' => $vcard->street,
            'House' => $vcard->house,
            'Building' => $vcard->building,
            'Apartment' => $vcard->apartment,
            'ExtraMessage' => $vcard->extra_message,
            'ContactEmail' => $vcard->contact_email,
            'Ogrn' => $vcard->ogrn,
            'ContactPerson' => $vcard->contact_person
        ];

        $result = $this->vCardResource->add(array_filter($data));

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        return $result->getIds()[0];
    }
    
    public function delete($id)
    {
        $result = $this->vCardResource->delete($id);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }
        
        return true;
    }
}
