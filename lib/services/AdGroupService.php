<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.04.16
 * Time: 20:27
 */

namespace app\lib\services;

use app\components\YandexConfig;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\Result;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\CampaignTemplate;
use app\models\Product;

/**
 * Class AdGroupService
 * @package app\lib\services
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class AdGroupService extends YandexService
{
    /**
     * @var AdGroupResource
     */
    protected $adGroupResource;

    /**
     * AdGroupService constructor.
     * @param AdGroupResource $resource
     */
    public function __construct(AdGroupResource $resource)
    {
        $this->adGroupResource = $resource;
    }

    /**
     * @param string[] $ids
     * @param string[] $fieldNames
     * @return Result
     */
    public function findByIds(array $ids, array $fieldNames = [])
    {
        return $this->adGroupResource->findByIds($ids, $fieldNames);
    }

    /**
     * Создание новой группы объявлений
     *
     * @param AdYandexCampaign $yandexAd
     * @return mixed
     * @throws YandexException
     */
    public function createAdGroup(AdYandexCampaign $yandexAd)
    {
        $campaignTemplate = $yandexAd->yandexCampaign->campaignTemplate;
        $regionIds = array_map('intval', explode(',', $campaignTemplate->regions));
        $data = [
            'RegionIds' => $regionIds,
            'Name' => date('d.m.Y') . ' ' . $yandexAd->ad->title,
            'CampaignId' => $yandexAd->yandexCampaign->yandex_id
        ];
        
        $result = $this->adGroupResource->add($data);
        
        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }
        
        return $result->getIds()[0];
    }

    /**
     * @param int|int[] $ids
     * @param CampaignTemplate $template
     * @return \app\lib\api\yandex\direct\query\ChangeResult
     */
    public function update($ids, CampaignTemplate $template)
    {
        $data = [];
        foreach ((array)$ids as $id) {
            $data[] = [
                'Id' => $id,
                'RegionIds' => array_map('intval', explode(',', $template->regions))
            ];
        }

        var_dump($data);

        return $this->adGroupResource->update($data);
    }

    /**
     * @param int|int[] $yandexGroupIds
     * @return \app\lib\api\yandex\direct\query\ChangeResult
     */
    public function delete($yandexGroupIds)
    {
        $yandexGroupIds = array_values((array)$yandexGroupIds);
        return $this->adGroupResource->delete($yandexGroupIds);
    }
}
