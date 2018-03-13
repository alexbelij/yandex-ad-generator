<?php

namespace app\lib\services;

use app\components\LoggerInterface;
use app\helpers\StringHelper;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdResource;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\AdTemplate;
use app\models\AdYandexGroup;
use app\models\search\TemplatesSearch;

/**
 * Сервис управления объявлениями
 *
 * Class AdService
 * @package app\lib\services
 */
class AdService extends YandexService
{
    const MAX_TITLE_LENGTH = 33;
    const MAX_MESSAGE_LENGTH = 75;
    const MAX_DISPLAY_URL_LENGTH = 20;

    /**
     * @var AdResource
     */
    protected $adResource;

    /**
     * AdService constructor.
     * @param AdResource $adResource
     */
    public function __construct(AdResource $adResource)
    {
        $this->adResource = $adResource;
    }

    /**
     * Публикация объявления
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @return int
     * @throws YandexException
     */
    public function createAd(AdYandexCampaign $yandexAd, ExtProduct $extProduct)
    {
        $textAdData = $this->getAdTemplate($yandexAd, $extProduct);

        if (!$textAdData) {
            throw new YandexException('Template for product not found');
        }

        $shop = $yandexAd->ad->product->shop;
        $rarelyServed = $yandexAd->adYandexGroup->serving_status == AdYandexGroup::SERVING_STATUS_RARELY_SERVED;

        $data = [
            'TextAd' => [
                'Href' => $shop->href_template ?
                    $this->getHrefByShopTemplate($shop->href_template, $extProduct, $rarelyServed) :
                    $this->getHrefWithUtm($extProduct, $yandexAd),
                'Mobile' => 'NO',
            ],
            'AdGroupId' => $yandexAd->yandex_adgroup_id
        ];

        $displayUrlPath = $this->getDisplayUrlPath($extProduct->getBrandTitle() . ' ' . $extProduct->title);

        if ($displayUrlPath) {
            $data['TextAd']['DisplayUrlPath'] = $displayUrlPath;
        }

        $product = $yandexAd->ad->product;

        if ($product->yandex_sitelink_id) {
            $data['TextAd']['SitelinkSetId'] = $product->yandex_sitelink_id;
        }

        if ($yandexAd->yandexCampaign->yandex_vcard_id) {
            $data['TextAd']['VCardId'] = $yandexAd->yandexCampaign->yandex_vcard_id;
        }

        $data['TextAd'] = array_merge($data['TextAd'], $textAdData);
        $this->log(
            'Обновление объявления: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $result = $this->adResource->add($data);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        return $result->getIds()[0];
    }

    /**
     * @param string $template
     * @param ExtProduct $extProduct
     * @param bool $rarelyServed
     * @return string
     */
    public function getHrefByShopTemplate($template, ExtProduct $extProduct, $rarelyServed = false)
    {
        $replacePairs = ['{href}' => urlencode($extProduct->getShortHref($rarelyServed))];

        return strtr($template, $replacePairs);
    }

    /**
     * Добавить к ссылке utm метку
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @return string
     */
    public function getHrefWithUtm(ExtProduct $extProduct, AdYandexCampaign $yandexAd)
    {
        $keyword = $extProduct->getBrandTitle() . ' ' . $extProduct->title;

        $utm = http_build_query([
            'utm_source' => 'yandex_direct',
            'utm_medium' => 'cpc',
            'utm_campaign' => $this->prepareUtmWord($yandexAd->yandexCampaign->title),
            'utm_term' => $this->prepareUtmWord($keyword)
        ]);

        $rarelyServed = $yandexAd->adYandexGroup->serving_status == AdYandexGroup::SERVING_STATUS_RARELY_SERVED;

        $href = $extProduct->getShortHref($rarelyServed);
        if (strpos($href, '?') === false) {
            return $href . '?' . $utm;
        } else {
            return $href . '&' . $utm;
        }
    }

    /**
     * @param string $word
     * @return mixed
     */
    protected function prepareUtmWord($word)
    {
        $word = preg_replace('#\s#', '-', $word);
        $word = preg_replace('#-{2,}#', '-', $word);
        return preg_replace('#[^\w-]#u', '_', $word);
    }

    /**
     * Отправить на модерацию
     *
     * @param AdYandexCampaign $yandexAd
     * @throws YandexException
     */
    public function toModerate(AdYandexCampaign $yandexAd)
    {
        $result = $this->adResource->moderate($yandexAd->yandex_ad_id);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }
    }

    /**
     * 
     * @param int[] $yandexAdIds
     * @param int $siteLinksId
     * @return \app\lib\api\yandex\direct\query\ChangeResult
     */
    public function updateSitelinksId($yandexAdIds, $siteLinksId)
    {
        $data = [];

        foreach ($yandexAdIds as $id) {
            $data[] = [
                'Id' => $id,
                'TextAd' => [
                    'SitelinkSetId' => $siteLinksId
                ]
            ];
        }

        return $this->adResource->update($data);
    }

    /**
     * Обновление объявления
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @param bool $withPattern
     * @return bool
     * @throws YandexException
     */
    public function update(AdYandexCampaign $yandexAd, ExtProduct $extProduct, $withPattern = false)
    {
        $textAdData = $this->getAdTemplate($yandexAd, $extProduct, $withPattern);
        if (!$textAdData) {
            throw new YandexException('Шаблон для объявления не найден');
        }

        $shop = $yandexAd->ad->product->shop;
        $rarelyServed = $yandexAd->adYandexGroup->serving_status == AdYandexGroup::SERVING_STATUS_RARELY_SERVED;

        $data = [
            'Id' => $yandexAd->yandex_ad_id,
            'TextAd' => [
                'Href' => $shop->href_template ?
                    $this->getHrefByShopTemplate($shop->href_template, $extProduct, $rarelyServed) :
                    $this->getHrefWithUtm($extProduct, $yandexAd),
            ]
        ];

        $displayUrlPath = $this->getDisplayUrlPath($extProduct->getBrandTitle() . ' ' . $extProduct->title);

        if ($displayUrlPath) {
            $data['TextAd']['DisplayUrlPath'] = $displayUrlPath;
        }

        $product = $yandexAd->ad->product;
        if ($product->yandex_sitelink_id) {
            $data['TextAd']['SitelinkSetId'] = $product->yandex_sitelink_id;
        }

        if (!empty($yandexAd->yandexCampaign->yandex_vcard_id)) {
            $data['TextAd']['VCardId'] = $yandexAd->yandexCampaign->yandex_vcard_id;
        }

        $data['TextAd'] = array_merge($data['TextAd'], $textAdData);

        $this->log(
            'Запрос на обновление объявления: ' .
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $result = $this->adResource->update($data);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        return $result->isSuccess();
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $externalProduct
     * @param bool $withPattern
     * @return array|null
     */
    public function getAdTemplate(AdYandexCampaign $yandexAd, ExtProduct $externalProduct, $withPattern = false)
    {
        $product = $yandexAd->ad->product;
        $placeholders = [
            '[brand]' => $externalProduct->getBrandTitle(),
            '[category]' => $externalProduct->getCategory(),
            '[price]' => $product->isManualPrice() ? $product->manual_price : $product->price,
            '[title]' => $yandexAd->ad->title,
            '[extTitle]' => $externalProduct->extTitle
        ];

        if ($withPattern) {
            $placeholders['[title]'] = '#' . $placeholders['[title]'] . '#';
        }

        $adTemplateSearch = new TemplatesSearch();
        $templates = $adTemplateSearch->search([
            'brandId' => $externalProduct->getBrandId(),
            'categoryId' => $externalProduct->getCategoryId(),
            'price' => $externalProduct->price,
            'shop_id' => $product->shop_id
        ])->query->orderBy('sort')->all();

        $yandexAd->template_id = null;

        foreach ($templates as $template) {
            $title = $yandexAd->ad->title;
            $message = strtr($template->message, $placeholders);

            if (mb_strlen($title) <= self::MAX_TITLE_LENGTH
                && mb_strlen($message) <= self::MAX_MESSAGE_LENGTH
            ) {
                $yandexAd->template_id = $template->id;
                $title = preg_replace('%[^\w.\s-!/]%u', ' ', $title);
                if ($withPattern) {
                    $title = "#$title#!";
                }

                return [
                    'Title' => preg_replace('#\s+#', ' ', $title),
                    'Text' => $message
                ];
            }
        }

        return null;
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $externalProduct
     * @return bool
     */
    public function hasTemplate(AdYandexCampaign $yandexAd, ExtProduct $externalProduct)
    {
        $template = $this->getAdTemplate($yandexAd, $externalProduct);

        return !empty($template);
    }

    /**
     * Снятие объявления
     *
     * @param AdYandexCampaign $yandexAd
     * @return bool
     */
    public function removeAd(AdYandexCampaign $yandexAd)
    {
        return $this->adResource->suspend($yandexAd->yandex_ad_id);
    }

    /**
     * @param array $ids
     * @return ChangeResult
     */
    public function removeAds($ids)
    {
        $ids = array_values($ids);
        //останавливаем показы
        $result = $this->adResource->suspend($ids);

        $successIds = [];
        /** @var ResultItem $resultItem */
        foreach ($result as $resultItem) {
            if (!$resultItem->hasError()) {
                $successIds[] = $resultItem->getId();
            }
        }

        if (!empty($successIds)) {
            $this->adResource->archive($successIds);
        }

        return $result;
    }

    /**
     * Возобновить показы
     *
     * @param AdYandexCampaign $yandexAd
     * @return bool
     */
    public function resume(AdYandexCampaign $yandexAd)
    {
        if ($yandexAd->state == Ad::STATE_ARCHIVED) {
            $this->adResource->unarchive($yandexAd->yandex_ad_id);
        }

        $result = $this->adResource->resume($yandexAd->yandex_ad_id);
        
        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }
        
        return true;
    }

    /**
     * @param $ids
     * @param $vCardId
     * @return \app\lib\api\yandex\direct\query\ChangeResult
     */
    public function updateVCard($ids, $vCardId)
    {
        $updateData = [];
        foreach ((array)$ids as $id) {
            $updateData[] = [
                'Id' => $id,
                'TextAd' => [
                    'VCardId' => $vCardId
                ]
            ];
        }
        
        return $this->adResource->update($updateData);
    }

    /**
     * @param string $str
     * @return string
     */
    protected function getDisplayUrlPath($str)
    {
        $str = preg_replace('#[^а-яА-Яa-zA-Z0-9№\s/\#-]#ui', '-', $str);
        $str = trim(StringHelper::truncateByWordsFromEnd($str, self::MAX_DISPLAY_URL_LENGTH));
        $str = preg_replace('#\s#', '-', $str);
        $str = preg_replace('#-{1,}#', '-', $str);

        return $str;
    }
}
