<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.04.16
 * Time: 23:22
 */

namespace app\lib\services;

use app\lib\api\yandex\direct\resources\SitelinksResource;
use app\models\Account;
use app\models\Shop;
use app\models\SitelinksItem;
use app\models\YandexSitelink;
use yii\helpers\ArrayHelper;

class SitelinksService extends YandexService
{
    /**
     * @var SitelinksResource
     */
    protected $resource;

    /**
     * SitelinksService constructor.
     * @param SitelinksResource $resource
     */
    public function __construct(SitelinksResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Создает быстрые ссылки для объявления магазина
     *
     * @param Shop $shop
     * @return YandexSitelink
     * @throws \app\lib\api\yandex\direct\exceptions\YandexException
     */
    public function createForShop(Shop $shop, Account $account)
    {
        /** @var SitelinksItem[] $items */
        $items = ArrayHelper::getValue($shop, 'sitelinks.sitelinksItems');

        if (empty($items)) {
            return null;
        }
        $items = $shop->sitelinks->sitelinksItems;

        $sitelinks = [];
        foreach ($items as $item) {
            $siteLinkItem = [
                'Title' => $item->title,
                'Href' => $item->href,
                'Description' => $item->description
            ];
            $sitelinks[] = array_filter($siteLinkItem);
        }

        $result = $this->resource->add([
            'Sitelinks' => $sitelinks
        ]);

        if (!$result->isSuccess()) {
            $this->throwExceptionFromResult($result);
        }

        return YandexSitelink::createOrUpdateSiteLink($shop->id, $account->id, $result->getIds()[0]);
    }
}