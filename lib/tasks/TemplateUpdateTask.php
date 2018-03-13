<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.05.16
 * Time: 9:55
 */

namespace app\lib\tasks;

use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\shop\gateways\InternalProductsGateway;
use app\lib\api\shop\gateways\ProductsGateway;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\services\AdService;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\Product;
use app\models\Shop;
use app\models\YandexUpdateLog;
use yii\helpers\ArrayHelper;

class TemplateUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'templateUpdate';

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @var ProductsGateway
     */
    protected $productsGateway;

    protected function init()
    {
        parent::init();
        $adResource = new AdResource($this->connection);

        $this->adService = new AdService($adResource);

        $this->productsGateway = InternalProductsGateway::factory($this->shop);
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
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();
        
        $query = AdYandexCampaign::find()
            ->joinWith(['ad.product'])
            ->andWhere('yandex_ad_id IS NOT NULL')
            ->andWhere([
                'shop_id' => $this->shop->id,
                'is_available' => 1
            ]);

        if (!empty($context['template_id'])) {
            $query->andWhere('template_id = :templateId or template_id IS NULL', [
                ':templateId' => $context['template_id']
            ]);
        }

        foreach ($query->batch() as $ads) {
            $productIds = ArrayHelper::getColumn($ads, 'ad.product.product_id');
            $apiProducts = $this->getProductsFromApi($productIds);
            /** @var AdYandexCampaign $ad */
            foreach ($ads as $ad) {
                $product = $ad->ad->product;
                if (empty($apiProducts[$product->product_id])) {
                    $this->logOperation(
                        $product, YandexUpdateLog::OPERATION_API_LOAD, YandexUpdateLog::STATUS_ERROR, 'Товар не получен от апи'
                    );
                    continue;
                }

                try {
                    $this->setAccountToken($ad->account_id);
                    $this->adService->update($ad, $apiProducts[$product->product_id]);

                    $this->logOperation($ad, YandexUpdateLog::OPERATION_UPDATE);
                } catch (YandexException $e) {
                    $this->logOperation(
                        $ad, YandexUpdateLog::OPERATION_UPDATE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
                    );
                }
            }
        }
    }
}
