<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.04.16
 * Time: 15:06
 */

namespace app\lib\services;

use app\components\YandexConfig;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\models\BrandAccount;
use app\models\CampaignTemplate;
use app\models\ExternalProduct;
use app\models\Settings;
use app\models\Shop;
use app\models\YandexCampaign;

/**
 * Сервис работы с кампаниями яндекса
 *
 * Class YandexCampaignService
 * @package app\lib\services
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class YandexCampaignService extends YandexService
{
    const MIN_DAILY_BUDGET = 301;

    /**
     * @var CampaignResource
     */
    protected $campaignResource;

    /**
     * YandexCampaignService constructor.
     * @param CampaignResource $resource
     */
    public function __construct(CampaignResource $resource)
    {
        $this->campaignResource = $resource;
    }

    /**
     * @param int $shopId
     * @param int $brandId
     * @param int $campaignTemplateId
     * @param int $accountId
     * @return YandexCampaign
     */
    public function getCampaign($shopId, $brandId, $campaignTemplateId, $accountId)
    {
        return YandexCampaign::find()
            ->andWhere([
                'shop_id' => $shopId,
                'brand_id' => $brandId,
                'campaign_template_id' => $campaignTemplateId,
                'account_id' => $accountId
            ])
            ->andWhere('products_count < :count', [':count' => YandexCampaign::MAX_CAMPAIGN_PRODUCTS])
            ->one();
    }

    /**
     * Создает новую кампанию
     *
     * @param ExtProduct $externalProduct
     * @param $shopId
     * @param CampaignTemplate $campaignTemplate
     * @param int $accountId
     * @return YandexCampaign
     * @throws YandexException
     */
    public function createCampaign(ExtProduct $externalProduct, $shopId, CampaignTemplate $campaignTemplate, $accountId)
    {
        $siblingsCount = $this->getSiblingsCampaignCount($externalProduct, $shopId, $campaignTemplate, $accountId);
        $name = $externalProduct->getBrandTitle() . ' - ' . $campaignTemplate->title;

        if ($siblingsCount > 0) {
            $name .= ' (' . ++$siblingsCount . ')';
        }

        $campaignData = $this->getCampaignData($campaignTemplate);
        $campaignData['Name'] = $name;
        $result = $this->campaignResource->add($campaignData);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        $campaignId = $result->getIds()[0];

        $campaign = new YandexCampaign([
            'shop_id' => $shopId,
            'brand_id' => $externalProduct->getBrandId(),
            'yandex_id' => $campaignId,
            'campaign_template_id' => $campaignTemplate->primaryKey,
            'title' => $name,
            'products_count' => 0,
            'account_id' => $accountId
        ]);

        $campaign->save();

        return $campaign;
    }

    /**
     * @param ExtProduct $extProduct
     * @param $shopId
     * @param CampaignTemplate $template
     * @param int $accountId
     * @return int|string
     */
    protected function getSiblingsCampaignCount(ExtProduct $extProduct, $shopId, CampaignTemplate $template, $accountId)
    {
        return YandexCampaign::find()
            ->andWhere([
                'shop_id' => $shopId,
                'brand_id' => $extProduct->getBrandId(),
                'campaign_template_id' => $template->primaryKey,
                'account_id' => $accountId
            ])->count();
    }

    /**
     * Возвращает настройки кампании в формате яндекса
     * полученные из шаблона кампании
     *
     * @param CampaignTemplate $campaignTemplate
     * @return mixed
     */
    protected function getCampaignData(CampaignTemplate $campaignTemplate)
    {
        $settings = $campaignTemplate->textCampaign->settings;
        $yandexSettingsMap = [];

        foreach ($settings as $name => $value) {
            $yandexSettingsMap[] = [
                'Option' => $name,
                'Value' => $value
            ];
        }

        $campaignData = [
            'StartDate' => date('Y-m-d'),
            'TextCampaign' => [
                'BiddingStrategy' => [
                    'Search' => [
                        'BiddingStrategyType' => $campaignTemplate->textCampaign->biddingStrategySearchType
                    ],
                    'Network' => [
                        'BiddingStrategyType' => $campaignTemplate->textCampaign->biddingStrategyNetworkType
                    ]
                ],
                'Settings' => $yandexSettingsMap
            ],
        ];

        $negativeKeywords = array_values(array_filter($this->parseKeywords($campaignTemplate->negative_keywords)));

        if (!empty($negativeKeywords)) {
            $campaignData['NegativeKeywords'] = [
                'Items' => array_values($this->parseKeywords($campaignTemplate->negative_keywords))
            ];
        }

        if (!empty($campaignTemplate->textCampaign->counterIds)) {
            $campaignData['TextCampaign']['CounterIds'] = [
                'Items' => array_map('intval', explode(',', $campaignTemplate->textCampaign->counterIds))
            ];
        }

        return $campaignData;
    }

    /**
     * Возвращает ключевые слова в виде массива из строки
     *
     * @param string $keywords
     * @return mixed
     */
    protected function parseKeywords($keywords)
    {
        $keywords = preg_split("#(\\s+|,|\r\n)#", $keywords, -1, PREG_SPLIT_NO_EMPTY);

        return array_unique(array_map('trim', $keywords));
    }

    /**
     * @param int $id
     * @return mixed|null
     */
    public function findById($id)
    {
        $result = $this->campaignResource->findByIds($id);
        
        if ($result->count() > 0) {
            return $result->first();
        } else {
            return null;
        }
    }

    /**
     * Обновление минус слов кампании
     *
     * @param int $id
     * @param string $keywords
     * @return bool
     */
    public function updateNegativeKeywords($id, $keywords)
    {
        $updateData = [
            'Id' => $id,
            'NegativeKeywords' => [
                'Items' => $this->parseKeywords($keywords)
            ]
        ];

        $result = $this->campaignResource->update($updateData);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        return $result->isSuccess();
    }

    /**
     * Обновление кампании
     *
     * @param int|int[] $ids
     * @return ChangeResult
     * @throws YandexException
     */
    public function updateCampaign($ids)
    {
        /** @var YandexCampaign[] $yandexCampaigns */
        $yandexCampaigns = YandexCampaign::find()->andWhere(['yandex_id' => $ids])->all();
        $updateData = [];
        foreach ($yandexCampaigns as $yandexCampaign) {
            $campaignData = $this->getCampaignData($yandexCampaign->campaignTemplate);
            $campaignData['Id'] = $yandexCampaign->yandex_id;
            $updateData[] = $campaignData;
        }

        if (empty($updateData)) {
            return null;
        }

        $result = $this->campaignResource->update($updateData);

        return $result;
    }

    /**
     * Удаление кампание, поочередно останавливает показы для кампании,
     * пытается удалить и заархиваровать
     *
     * @param int|int[] $id
     */
    public function remove($id)
    {
        $this->campaignResource->suspend($id);
        $this->campaignResource->delete($id);
        $this->campaignResource->archive($id);
    }
}
