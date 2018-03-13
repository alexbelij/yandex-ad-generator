<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.10.16
 * Time: 20:38
 */

namespace app\lib\services;

use app\lib\api\yandex\direct\exceptions\YandexException;
use app\models\AdYandexCampaign;
use app\models\YandexCampaign;

/**
 * Class YandexAdErrorFixer
 * @package app\lib\services
 */
class YandexAdErrorHandler
{
    const WRONG_OBJECT_STATUS = 8300;
    const OBJECT_NOT_FOUND = 8800;

    const MESSAGE_CAMPAIGN_IS_ARCHIVE = 'Кампания помещена в архив';
    const MESSAGE_CAMPAIGN_IS_ARCHIVE2 = 'Запрещено изменять заархивированную кампанию';
    const MESSAGE_AD_IS_ARCHIVED = 'Объявление заархивировано и не может быть запущено';
    const MESSAGE_WRONG_STATUS = 'Объявление является черновиком и не может быть запущено';
    const AD_NOT_FOUND = 'Объявление не найдено';
    const VCARD_NOT_FOUND = 'Визитка не найдена';

    /**
     * @param YandexException $e
     * @param AdYandexCampaign $yandexAd
     * @return bool
     * @throws YandexException
     */
    public function handle(YandexException $e, AdYandexCampaign $yandexAd)
    {
        if ($e->getCode() == self::WRONG_OBJECT_STATUS) {
            return $this->handleWrongObjectStatus($e, $yandexAd);
        } elseif ($e->getCode() == self::OBJECT_NOT_FOUND) {
            return $this->handleObjectNotFound($e, $yandexAd);
        }
    }

    /**
     * @param YandexException $e
     * @param AdYandexCampaign $yandexAd
     * @return bool
     */
    protected function handleWrongObjectStatus(YandexException $e, AdYandexCampaign $yandexAd)
    {
        switch ($e->getDetails()) {
            case self::MESSAGE_CAMPAIGN_IS_ARCHIVE:
            case self::MESSAGE_CAMPAIGN_IS_ARCHIVE2:
                $this->fixArchiveCampaign($yandexAd);
                break;
            case self::MESSAGE_AD_IS_ARCHIVED:
            case self::MESSAGE_WRONG_STATUS:
                $yandexAd->delete();
                break;
        }

        return true;
    }

    /**
     * @param YandexException $e
     * @param AdYandexCampaign $yandexAd
     * @return bool
     */
    protected function handleObjectNotFound(YandexException $e, AdYandexCampaign $yandexAd)
    {
        switch ($e->getDetails()) {
            case self::AD_NOT_FOUND:
                $yandexAd->delete();
                break;
        }

        return true;
    }

    /**
     * Если кампания по какой-то причине оказалась заархивирована
     * то удаляем ее из генератора и всю информацию о размещенных
     * в ней объявлениях
     *
     * @param AdYandexCampaign $yandexAd
     */
    protected function fixArchiveCampaign(AdYandexCampaign $yandexAd)
    {
        AdYandexCampaign::deleteAll(['yandex_campaign_id' => $yandexAd->yandex_campaign_id]);
        YandexCampaign::deleteAll(['id' => $yandexAd->yandex_campaign_id]);
    }
}
