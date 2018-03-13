<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 15.10.16
 * Time: 15:41
 */

namespace app\controllers\generator;

use app\controllers\BaseController;
use app\helpers\ArrayHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\gateways\cached\BrandsCachedGateway;
use app\lib\services\BrandCountService;
use app\models\BrandAccount;
use app\models\search\BrandAccountSearch;
use app\models\Shop;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class BrandAccountsController
 * @package app\controllers\generator
 */
class BrandAccountsController extends BaseController
{
    /**
     * @param $shopId
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionIndex($shopId)
    {
        $searchModel = new BrandAccountSearch();
        $searchModel->load($this->request->queryParams);

        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new BadRequestHttpException('Shop not found');
        }

        $searchModel->shopId = $shopId;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(),
            'brandCountService' => new BrandCountService($shop)
        ]);
    }

    /**
     * @param $brandId
     * @param $shopId
     * @return string
     */
    public function actionUpdate($brandId, $shopId)
    {
        $model = $this->getModel($shopId, $brandId);

        if ($model->load($this->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(
                Url::to([
                    '/generator/brand-accounts',
                    'brandId' => $model->brand_id,
                    'shopId' => $model->shop_id
                ])
            );
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * @param int $shopId
     * @param int $brandId
     * @return BrandAccount
     * @throws NotFoundHttpException
     */
    protected function getModel($shopId, $brandId)
    {
        $model = BrandAccount::find()
            ->andWhere(['shop_id' => $shopId, 'brand_id' => $brandId])
            ->one();
        if (!$model) {
            $model = new BrandAccount([
                'shop_id' => $shopId,
                'brand_id' => $brandId
            ]);
        }

        $shop = Shop::findOne($shopId);

        if (!$shopId) {
            throw new NotFoundHttpException('shop not found');
        }

        $brandInfo = $this->getBrandInfo($brandId, $shop);
        if (!$brandInfo) {
            throw new NotFoundHttpException('Brand not found');
        }

        $model->brandTitle = $brandInfo['title'];

        return $model;
    }

    /**
     * @param int $brandId
     * @param Shop $shop
     * @return mixed
     */
    protected function getBrandInfo($brandId, Shop $shop)
    {
        static $brands = [];

        if (empty($brands)) {
            $brandsGateway = new BrandsCachedGateway(BrandsGateway::factory($shop));
            $brands = ArrayHelper::index($brandsGateway->getList(), 'id');
        }

        return ArrayHelper::getValue($brands, $brandId);
    }

}
