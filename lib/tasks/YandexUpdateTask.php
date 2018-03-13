<?php

namespace app\lib\tasks;

use app\helpers\JsonHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\shop\gateways\InternalProductsGateway;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\exceptions\ConnectionException;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\api\yandex\direct\resources\SitelinksResource;
use app\lib\api\yandex\direct\resources\VCardsResource;
use app\lib\LoggedStub;
use app\lib\services\AdGroupService;
use app\lib\services\AdService;
use app\lib\services\KeywordsService;
use app\lib\services\PointsForecast;
use app\lib\services\SitelinksService;
use app\lib\services\VcardsService;
use app\lib\services\YandexAdErrorHandler;
use app\lib\services\YandexCampaignService;
use app\lib\variationStrategies\DefaultStrategy;
use app\models\Account;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\BrandAccount;
use app\models\CampaignTemplate;
use app\models\Product;
use app\models\search\YandexAdUpdateSearch;
use app\models\Shop;
use app\models\Vcard;
use app\models\YandexCampaign;
use app\models\YandexSitelink;
use app\models\YandexUpdateLog;
use app\helpers\ArrayHelper;

/**
 * Таск на обновление объявлений с директом
 *
 * Class YandexUpdateTask
 * @package app\lib\tasks
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class YandexUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'yandexUpdate';

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var AdGroupService
     */
    protected $adGroupService;

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @var KeywordsService
     */
    protected $keywordsService;

    /**
     * @var YandexCampaignService
     */
    protected $campaignService;

    /**
     * @var InternalProductsGateway
     */
    protected $productsGateway;

    /**
     * @var SitelinksService
     */
    protected $siteLinksService;

    /**
     * @var VcardsService
     */
    protected $vcardService;

    /**
     * Изменилась ли цена товара
     *
     * @var array
     */
    protected $needPriceUpdate = [];

    /**
     * @var int
     */
    protected $revision;

    /**
     * @var YandexAdErrorHandler
     */
    protected $yandexAdFixer;

    /**
     * @var YandexCampaign[]
     */
    protected $yandexCampaigns = [];

    /**
     * @var bool[]
     */
    protected $isYandexCampaignChange = [];

    /**
     * @var array
     */
    protected $usedAccountIds = [];

    /**
     * Служебная информация о ходе выполнения операции
     * @var array
     */
    protected $statistics = [
        'errors' => [],
        'updated' => [],
        'deleted' => [],
        'created' => []
    ];

    protected function init()
    {
        parent::init();

        $campaignResource = new CampaignResource($this->connection);
        $adGroupResource = new AdGroupResource($this->connection);
        $adResource = new AdResource($this->connection);
        $keywordsResource = new KeywordsResource($this->connection);

        $this->adGroupService = new AdGroupService($adGroupResource);
        $this->adService = new AdService($adResource);
        $this->adService->setLogger($this->getLogger());
        $this->keywordsService = new KeywordsService($keywordsResource);
        $this->campaignService = new YandexCampaignService($campaignResource);
        $this->siteLinksService = new SitelinksService(new SitelinksResource($this->connection));
        $this->vcardService = new VcardsService(new VCardsResource($this->connection));

        $this->productsGateway = InternalProductsGateway::factory($this->shop);
        $this->yandexAdFixer = new YandexAdErrorHandler();
    }

    /**
     * @param int[] $productIds
     * @return ExtProduct[]
     */
    protected function getProductsFromApi($productIds)
    {
        $apiProducts = $this->productsGateway->findByIds($productIds);
        $result = [];

        foreach ($apiProducts as $product) {
            $result[$product['id']] = new ExtProduct($product);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function execute($params = [])
    {
        $searchModel = new YandexAdUpdateSearch();
        $context = $this->task->getContext();
        $adQuery = $searchModel->search(
            array_merge(['shop' => $this->task->shop], $context)
        );

        $this->logger->log($adQuery->createCommand()->getRawSql());

        $this->revision = Ad::getRevision($this->task->shop_id) + 1;

        /** @var CampaignTemplate[] $campaignTemplates */
        $campaignTemplates = CampaignTemplate::find()
            ->andWhere(['shop_id' => $this->shop->primaryKey])
            ->all();

        $contextAccount = null;
        if ($contextAccountId = ArrayHelper::getValue($this->task->getContext(), 'accountId')) {
            $contextAccount = Account::findOne($contextAccountId);
        }

        if (!empty($context['accountId'])) {
            $this->usedAccountIds[] = $context['accountId'];
        }

        /** @var Ad[] $ads */
        foreach ($adQuery->batch(100) as $ads) {
            $productIds = ArrayHelper::getColumn($ads, 'product.product_id');
            $externalProducts = $this->getProductsFromApi($productIds);

            if (empty($externalProducts)) {
                $this->logger->log('Товары не были получены: ' . json_encode($productIds));
            }

            foreach ($ads as $ad) {

                $product = $ad->product;
                $shop = Shop::findOne($this->shop->id);
                $account = $contextAccount ?: BrandAccount::getAccountByBrand($shop, $product->brand_id);

                $this->logger->log('Используем аккаунт: ' . $account->title);

                if (trim($ad->title) == DefaultStrategy::LIMIT_REACH_MESSAGE) {
                    continue;
                }

                $ad->revision = $this->revision;
                $ad->save();

                if (!$account->hasAvailableUnits() && $this->pointsCalculator->getTotal() > 0) {

                    if ($contextAccount) {
                        throw new YandexException('Недостаточно баллов для выполнения операции');
                    }

                    continue;
                }

                $this->connection->setAuthIdentity(new ApiAccountIdentity($account));

                $this->logger->log('Обновление товара с id - ' . $product->id);
                if (empty($externalProducts[$product->product_id])) {
                    $this->productNotLoadFromApi($product);
                    //$product->delete();
                    continue;
                }

                //описание товара полученное через api
                /** @var ExtProduct $externalProduct */
                $externalProduct = $externalProducts[$product->product_id];

                if (!isset($this->needPriceUpdate[$product->id])) {
                    $this->needPriceUpdate[$product->id] = ($product->price != round($externalProduct->price));
                    if ($this->needPriceUpdate[$product->id]) {
                        $oldPrice = $product->price;
                        $product->price = round($externalProduct->price);
                        $product->save();
                        if ($product->hasErrors()) {
                            $errors = $product->getFirstErrors();
                            throw new YandexException(reset($errors));
                        }
                        $this->logger->log("Обновление цены: старая - {$oldPrice}, новая - {$product->price}");
                    }
                }

                $this->updateSiteLinks($product, $account);

                if (!in_array($account->id, $this->usedAccountIds)) {
                    $this->usedAccountIds[] = $account->id;
                }

                foreach ($campaignTemplates as $campaignTemplate) {
                    if (!$campaignTemplate->isAllowForBrand($externalProduct->getBrandId())) {
                        continue;
                    }

                    $yandexCampaignsIds = ArrayHelper::getColumn(
                        $this->getYandexCampaigns($campaignTemplate, $account), 'id'
                    );

                    /** @var AdYandexCampaign[] $yandexAds */
                    $yandexAds = AdYandexCampaign::find()
                        ->andWhere([
                            'ad_id' => $ad->primaryKey,
                            'account_id' => $account->id,
                            'yandex_campaign_id' => $yandexCampaignsIds
                        ])
                        ->all();

                    if (empty($yandexAds)) {
                        $yandexCampaign = $this->getOrCreateCampaign($externalProduct, $campaignTemplate, $account);
                        $yandexAd = new AdYandexCampaign([
                            'ad_id' => $ad->primaryKey,
                            'yandex_campaign_id' => $yandexCampaign->primaryKey,
                            'account_id' => $account->id
                        ]);
                        $yandexAd->save();
                        $yandexAds[] = $yandexAd;
                    }

                    foreach ($yandexAds as $yandexAd) {

                        //если объявление не было размещено, необходимо заново получить группу, т.к. уже может не быть места для объявления
                        if (!$yandexAd->yandex_campaign_id || !$yandexAd->yandex_ad_id) {
                            $yandexCampaign = $this->getOrCreateCampaign(
                                $externalProduct, $campaignTemplate, $account
                            );
                            $yandexAd->yandex_campaign_id = $yandexCampaign->id;
                            $yandexAd->save();
                            $yandexAd->refresh();
                        } else {
                            $yandexCampaign = $yandexAd->yandexCampaign;
                        }
                        //создание визиток
                        if (!$yandexCampaign->yandex_vcard_id) {
                            $vcardId = $this->createVcardFor($yandexCampaign);
                            if ($vcardId) {
                                $yandexCampaign->yandex_vcard_id = $vcardId;
                                $yandexCampaign->save();
                            }
                        }
                        $this->processUpdate($yandexAd, $externalProduct);
                    }
                }
            }
        }

        $this->removeOldRevisionAds();

        //обновление прогноза использования баллов
        $pointsForecast = new PointsForecast();
        $pointsForecast->update($this->task->shop);
    }

    /**
     * @param CampaignTemplate $campaignTemplate
     * @param Account $account
     * @return YandexCampaign[]
     */
    protected function getYandexCampaigns(CampaignTemplate $campaignTemplate, Account $account)
    {
        $key = $campaignTemplate->id . ':' . $account->id;
        if (!empty($this->isYandexCampaignChange[$campaignTemplate->id])
            || empty($this->yandexCampaigns[$key])
        ) {
            $this->yandexCampaigns[$key] = YandexCampaign::find()
                ->andWhere([
                    'campaign_template_id' => $campaignTemplate->id,
                    'shop_id' => $this->task->shop_id,
                    'account_id' => $account->id
                ])->all();
            $this->isYandexCampaignChange[$campaignTemplate->id] = false;
        }

        return $this->yandexCampaigns[$key];
    }

    /**
     * Снятие с публикации объявлений, которые размещены и не попали
     * в выборку по условию (мин/макс цена, бренд)
     */
    protected function removeOldRevisionAds()
    {
        $brandIds = ArrayHelper::getValue($this->task->getContext(), 'brandIds');

        if (empty($brandIds) || empty($this->usedAccountIds)) {
            return;
        }

        //список объявлений для снятия с показов
        $toSuspendQuery = AdYandexCampaign::find()
            ->innerJoinWith('ad.product')
            ->andWhere(['<', '{{%ad}}.revision', $this->revision])
            ->andWhere('yandex_campaign_id IS NOT NULL')
            ->andWhere([
                '{{%product}}.shop_id' => $this->task->shop_id,
                'is_published' => 1,
                '{{%product}}.brand_id' => $brandIds,
                'ad_yandex_campaign.account_id' => $this->usedAccountIds
            ]);

        $this->getLogger()->log('Удаление объявлений: ' . $toSuspendQuery->createCommand()->getRawSql());

        /** @var AdYandexCampaign[] $yandexAds */
        foreach ($toSuspendQuery->batch(100) as $yandexAds) {
            foreach ($yandexAds as $yandexAd) {
                $account = BrandAccount::getAccountByBrand($this->shop, $yandexAd->ad->product->brand_id);
                if ($account->hasAvailableUnits()) {
                    $this->removeAd($yandexAd);
                }
            }
        }
    }

    /**
     * Соаздание визитной катрочки для кампании
     *
     * @param YandexCampaign $yaCampaign
     * @return mixed|null
     */
    protected function createVcardFor(YandexCampaign $yaCampaign)
    {
        static $cache = [];

        if (!array_key_exists($yaCampaign->id, $cache)) {
            $vcard = Vcard::find()->andWhere(['shop_id' => $yaCampaign->shop_id])->one();
            if (!$vcard) {
                $cache[$yaCampaign->id] = null;
                return null;
            }

            try {
                $cache[$yaCampaign->id] = $this->vcardService->createCardFor($yaCampaign, $vcard);
                $this->logOperation($vcard, YandexUpdateLog::OPERATION_VCARD_CREATE);
            } catch (YandexException $e) {
                $this->logOperation(
                    $vcard, YandexUpdateLog::OPERATION_VCARD_CREATE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
                );
                $cache[$yaCampaign->id] = null;
            }
        }

        return $cache[$yaCampaign->id];
    }

    /**
     * Возвращает ид быстрых ссылок
     *
     * @param Account $account
     * @return null
     */
    protected function getSiteLinksId(Account $account)
    {
        static $cache = [];

        if (!array_key_exists($account->id, $cache)) {
            /** @var YandexSitelink $yandexSitelink */
            $yandexSitelink = YandexSitelink::find()
                ->andWhere([
                    'account_id' => $account->id,
                    'shop_id' => $this->shop->id
                ])->one();

            if ($yandexSitelink) {
                $cache[$account->id] = $yandexSitelink->yandex_id;
            } else {
                $yandexSitelink = $this->siteLinksService->createForShop($this->shop, $account);
                if ($yandexSitelink) {
                    $cache[$account->id] = $yandexSitelink->yandex_id;
                    $this->logOperation($yandexSitelink, YandexUpdateLog::OPERATION_SITELINKS_UPDATE);
                } else {
                    $cache[$account->id] = null;
                }
            }
        }

        return $cache[$account->id];
    }

    /**
     * @param ExtProduct $apiProduct
     * @param CampaignTemplate $campaignTemplate
     * @param Account $account
     * @return YandexCampaign
     * @throws YandexException
     */
    protected function getOrCreateCampaign(ExtProduct $apiProduct, CampaignTemplate $campaignTemplate, Account $account)
    {
        $yaCampaign = $this->campaignService->getCampaign(
            $this->shop->id, $apiProduct->getBrandId(), $campaignTemplate->primaryKey, $account->id
        );
        if (!$yaCampaign) {
            $yaCampaign = $this->createCampaign($apiProduct, $campaignTemplate, $account);
            $this->isYandexCampaignChange[$campaignTemplate->id] = true;
        }

        return $yaCampaign;
    }

    /**
     * Обработка ситуации, когда товар не был получен из api
     * @param Product $product
     * @return null
     */
    protected function productNotLoadFromApi(Product $product)
    {
        //елси товара и не было в наличии, возможно товар удалили из базы магазина
        if (!$product->is_available) {
            return null;
        }

        $this->logger->log('Товар не загружен через апи');

        foreach ($product->ads as $ad) {
            foreach ($ad->yandexAds as $yandexAd) {
                if (!empty($yandexAd->yandex_ad_id)) {
                    $this->removeAd($yandexAd);
                }
            }
        }

    }

    /**
     * Создает объект для логирования операции
     *
     * @param Product $product
     * @param string $operation
     * @param string $status
     * @return YandexUpdateLog
     */
    protected function createUpdateLogForProduct(
        Product $product, $operation, $status = YandexUpdateLog::STATUS_SUCCESS
    ) {
        return new YandexUpdateLog([
            'shop_id' => $this->shop->id,
            'task_id' => $this->task->id,
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'operation' => $operation,
            'status' => $status
        ]);
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @throws TaskException
     */
    protected function processUpdate(AdYandexCampaign $yandexAd, ExtProduct $extProduct)
    {
        $product = $yandexAd->ad->product;

        if (!$extProduct->isAvailable) {
            $product->is_available = 0;
            if (!$product->save()) {
                throw new TaskException(
                    'Ошибка при сохранении товара: ' . ArrayHelper::first($product->getFirstErrors())
                );
            }
            $yandexAd->refresh();
        }
        //$this->getLogger()->log(json_encode($extProduct->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (empty($yandexAd->yandex_ad_id) && $extProduct->isAvailable) {
            $this->createAd($yandexAd, $extProduct);
        } elseif ($this->isNeedRemoveAd($yandexAd, $extProduct)) {
            $this->removeAd($yandexAd);
        } elseif ($this->isNeedRestore($yandexAd, $extProduct)) {
            $this->restoreAd($yandexAd, $extProduct);
        } elseif ($this->isNeedUpdateAd($yandexAd, $extProduct)) {
            $this->updateAd($yandexAd, $extProduct);
        }
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @return bool
     */
    protected function isNeedRestore(AdYandexCampaign $yandexAd, ExtProduct $extProduct)
    {
        return $yandexAd->yandex_ad_id &&
            $extProduct->isAvailable &&
            !$yandexAd->is_published;
    }

    /**
     * Нужно ли снять объявление в директе
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @return bool
     */
    protected function isNeedRemoveAd(AdYandexCampaign $yandexAd, ExtProduct $extProduct)
    {
        return (!$extProduct->isAvailable || $yandexAd->ad->is_deleted)
            && $yandexAd->is_published
            && $yandexAd->yandex_ad_id;
    }

    /**
     * Обновление быстрых ссылок
     *
     * @param Product $product
     * @param Account $account
     */
    protected function updateSiteLinks(Product $product, Account $account)
    {
        try {
            $product->yandex_sitelink_id = $this->getSiteLinksId($account);
            $product->save();
        } catch (YandexException $e) {
            $this->logOperation(
                $product,
                YandexUpdateLog::OPERATION_CREATE_SITELINKS,
                YandexUpdateLog::STATUS_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Нужно ли обновлять объявление (обновляем только активные объявления)
     * если объявление не активно, то снимаем его
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $apiProduct
     * @return bool
     */
    protected function isNeedUpdateAd(AdYandexCampaign $yandexAd, ExtProduct $apiProduct)
    {
        $product = $yandexAd->ad->product;
        //обновляем только товары, которые есть в наличии
        if (!$product->is_available ||
            !$apiProduct->isAvailable ||
            !$yandexAd->yandex_ad_id ||
            !$yandexAd->is_published
        ) {
            return false;
        }

        if (($product->isAutomaticPrice() &&
            !empty($this->needPriceUpdate[$product->primaryKey]))) {
            $this->logger->log('Изменение цены у объявления:' . JsonHelper::encodeModelPretty($yandexAd->ad));
            return true;
        } elseif ((strtotime($yandexAd->ad->updated_at) > strtotime($yandexAd->uploaded_at))) {
            $this->logger->log('Объявление было изменено' . JsonHelper::encodeModelPretty($yandexAd->ad));
            return true;
        }

        return false;
    }

    /**
     * Возобновить показы
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $externalProduct
     */
    protected function restoreAd(AdYandexCampaign $yandexAd, ExtProduct $externalProduct)
    {
        try {

            $this->logger->log("Возобновление показов для объявления:\r\n" . JsonHelper::encodeModelPretty($yandexAd->ad));
            $this->adService->resume($yandexAd);
            $yandexAd->is_published = 1;
            $yandexAd->save();

            $product = $yandexAd->ad->product;
            if ($externalProduct->isAvailable && !$product->is_available) {
                $product->is_available = 1;
                $product->save();
            }

            $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_RESUME);
            $this->updateAd($yandexAd, $externalProduct);

        } catch (YandexException $e) {

            $this->logOperation(
                $yandexAd, YandexUpdateLog::OPERATION_RESUME, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
            );
            $this->yandexAdFixer->handle($e, $yandexAd);

        }
    }

    /**
     * Обновление объявления
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $extProduct
     * @throws YandexException
     */
    protected function updateAd(AdYandexCampaign $yandexAd, ExtProduct $extProduct)
    {
        $product = $yandexAd->ad->product;

        $this->logger->log(sprintf('Обновление объявления для товара %d, %s', $product->id, $product->title));
        $this->logger->log(JsonHelper::encodeModelPretty($yandexAd));
        $this->logger->log('Обновление объявления: ' . JsonHelper::encodeModelPretty($yandexAd->ad));

        try {

            if (!$this->adService->hasTemplate($yandexAd, $extProduct)) {
                throw new YandexException('Шаблон для объявления не найден');
            }

            $result = $this->keywordsService->updateKeywords($yandexAd);
            if ($result && !$result->isSuccess()) {
                $message = $this->getKeywordsErrorMessage($result);
                $this->logOperation(
                    $yandexAd, YandexUpdateLog::OPERATION_KEYWORDS_UPDATE, YandexUpdateLog::STATUS_ERROR, $message
                );
            }
            $this->adService->update($yandexAd, $extProduct);
            $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_UPDATE);
            $yandexAd->updateUploadDate();

        } catch (YandexException $e) {
            $this->logOperation(
                $yandexAd, YandexUpdateLog::OPERATION_UPDATE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
            );
            $this->yandexAdFixer->handle($e, $yandexAd);
        }
    }

    /**
     * Возвращает текст ошибки для ключевиков
     *
     * @param ChangeResult $result
     * @return string
     */
    protected function getKeywordsErrorMessage(ChangeResult $result)
    {
        $message = '<ul>';
        /** @var ResultItem $resultItem */
        foreach ($result as $resultItem) {
            if ($resultItem->hasError()) {
                $message .= '<li>' . $resultItem->firstError()->errorInfo() . '</li>';
            }
        }
        $message .= '</ul>';

        return $message;
    }

    /**
     * Удаление объявления
     *
     * @param AdYandexCampaign $yandexAd
     */
    protected function removeAd(AdYandexCampaign $yandexAd)
    {
        $product = $yandexAd->ad->product;
        $this->logger->log(sprintf('Снимаем с публикации объявление %d, %s', $product->primaryKey, $product->title));
        $this->logger->log('Объявление: ' . JsonHelper::encodeModelPretty($yandexAd->ad));

        try {
            $this->adService->removeAd($yandexAd);
            //$this->adGroupService->delete($yandexAd->yandex_adgroup_id);
            $yandexAd->is_published = false;
            $yandexAd->updateUploadDate();
            $yandexAd->save();
            $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_REMOVE);
        } catch (YandexException $e) {
            $this->logOperation(
                $yandexAd, YandexUpdateLog::OPERATION_REMOVE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
            );
        }
    }

    /**
     * Создание нового объявления
     *
     * @param AdYandexCampaign $yandexAd
     * @param ExtProduct $apiProduct
     * @throws \Exception
     */
    protected function createAd(AdYandexCampaign $yandexAd, ExtProduct $apiProduct)
    {
        if ($yandexAd->ad->title == DefaultStrategy::LIMIT_REACH_MESSAGE) {
            return;
        }
        $product = $yandexAd->ad->product;
        $this->logger->log(sprintf('Публикация объявления %d, %s', $product->id, $product->title));

        if (!$this->adService->getAdTemplate($yandexAd, $apiProduct)) {
            $this->logOperation(
                $yandexAd,
                YandexUpdateLog::OPERATION_CREATE,
                YandexUpdateLog::STATUS_ERROR,
                'Не найден шаблон объявления'
            );
            return;
        }

        try {

            if (empty($yandexAd->yandex_adgroup_id)) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    $keywordsCount = count($yandexAd->ad->getKeywordsArray());
                    $group = AdYandexGroup::findSuitable($yandexAd, $keywordsCount);
                    $group = null; // не буду пока таки выпиливать код напрочь

                    if (!$group) {
                        $yandexAd->yandex_adgroup_id = $this->adGroupService->createAdGroup($yandexAd);
                        $group = new AdYandexGroup([
                            'yandex_adgroup_id' => $yandexAd->yandex_adgroup_id,
                            'yandex_campaign_id' => $yandexAd->yandex_campaign_id,
                            'ads_count' => 1,
                        ]);

                    } else {
                        $yandexAd->yandex_adgroup_id = $group->yandex_adgroup_id;
                    }

                    $group->keywords_count += $keywordsCount;
                    $group->ads_count += 1;

                    $group->save();
                    $yandexAd->ad_yandex_group_id = $group->primaryKey;
                    $yandexAd->save();
                } catch (\Exception $e) {
                    $this->logger->log('Error occured while ad group processed: ' . $e->getMessage());
                    $transaction->rollBack();

                    throw $e;
                }

                $transaction->commit();
            }

            $result = $this->keywordsService->createKeywordsFor($yandexAd);
            
            if (!$result->isSuccess()) {
                $message = $this->getKeywordsErrorMessage($result);
                $this->logOperation(
                    $yandexAd, YandexUpdateLog::OPERATION_KEYWORDS_CREATE, YandexUpdateLog::STATUS_ERROR, $message
                );
            }

            //ключевые слова не были добавлены
            if (count($result->getResult()) == count($result->getErrors())) {
                throw new YandexException('Ключевые слова не были добавлены');
            }

            try {
                $yandexAd->yandex_ad_id = $this->adService->createAd($yandexAd, $apiProduct);
            } catch (YandexException $e) {
                if ($e->getDetails() == YandexAdErrorHandler::VCARD_NOT_FOUND) {
                    $yandexAd->yandexCampaign->yandex_vcard_id = $this->createVcardFor($yandexAd->yandexCampaign);
                    $yandexAd->yandexCampaign->save();
                    $yandexAd->refresh();
                    $yandexAd->yandex_ad_id = $this->adService->createAd($yandexAd, $apiProduct);
                } else {
                    throw $e;
                }
            }

            $yandexAd->is_published = true;
            $yandexAd->save();

            //отправка объявления на модерацию не допускается без ключевых слов
            //link: https://tech.yandex.ru/direct/doc/ref-v5/ads/moderate-docpage/
            $this->adService->toModerate($yandexAd);

            $yandexAd->yandexCampaign->incrementProductsCount();

            $yandexAd->updateUploadDate();
            $this->logger->log('Публикация объявления: ' . JsonHelper::encodeModelPretty($yandexAd->ad));
            $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_CREATE);

        } catch (YandexException $e) {
            $this->logOperation(
                $yandexAd, YandexUpdateLog::OPERATION_CREATE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
            );
            if ($e instanceof ConnectionException) {
                $this->logger->log('Запрос на создание объявления: ' . json_encode($e->getRequestData(), JSON_PRETTY_PRINT));
            }
            $this->yandexAdFixer->handle($e, $yandexAd);
        }
    }
    
    /**
     * Создание новой кампании
     * 
     * @param ExtProduct $externalProduct
     * @param CampaignTemplate $campaignTemplate
     * @param Account $account
     * @return YandexCampaign
     * @throws YandexException
     */
    protected function createCampaign(ExtProduct $externalProduct, CampaignTemplate $campaignTemplate, Account $account)
    {
        try {
            $yaCampaign = $this->campaignService->createCampaign($externalProduct, $this->shop->id, $campaignTemplate, $account->id);
            $this->logOperation($yaCampaign, YandexUpdateLog::OPERATION_CREATE);
            $this->logger->log('Создание кампании: ' . JsonHelper::encodeModelPretty($yaCampaign));
        } catch (YandexException $e) {
            $this->logOperation(
                new LoggedStub(['type' => 'campaign']),
                YandexUpdateLog::OPERATION_CREATE,
                YandexUpdateLog::STATUS_ERROR,
                'Code: ' . $e->getCode() . ', message: ' . $e->getMessage()
            );

            throw $e;
        }

        return $yaCampaign;
    }
}
