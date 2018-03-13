<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 15.10.16
 * Time: 14:59
 */

namespace app\models\search;

use app\helpers\ArrayHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\query\BrandQuery;
use app\lib\provider\ApiDataProvider;
use app\models\BrandAccount;
use app\models\Shop;
use yii\base\Model;

/**
 * Class BrandSearch
 * @package app\models\search
 */
class BrandAccountSearch extends Model
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $id;

    /**
     * @var bool
     */
    public $onlyActive = true;

    /**
     * @var string
     */
    public $shopId;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name', 'id', 'onlyActive'], 'safe'],
            ['shopId', 'required']
        ];
    }

    /**
     * @param array $params
     * @return ApiDataProvider|null
     */
    public function search(array $params = [])
    {
        $this->load($params);

        if (!$this->validate()) {
            return null;
        }

        $brandQuery = new BrandQuery();

        $brandQuery->onlyActive($this->onlyActive);

        if ($this->name) {
            $brandQuery->filterByTitle($this->name);
        }

        if ($this->id) {
            $brandQuery->byIds($this->id);
        }

        $brandQuery->setOrder('title ASC');

        /** @var Shop $shop */
        $shop = Shop::findOne($this->shopId);
        $brandsGateway = BrandsGateway::factory($shop);

        $dataProvider = new ApiDataProvider([
            'query' => $brandQuery,
            'gateway' => $brandsGateway,
            'processStrategy' => function ($models) use ($shop) {
                if (empty($models)) {
                    return [];
                }

                $brandIds = ArrayHelper::getColumn($models, 'id');

                $brandAccounts = BrandAccount::find()
                    ->andWhere([
                        'brand_id' => $brandIds,
                        'shop_id' => $shop->id
                    ])
                    ->indexBy('brand_id')
                    ->with('account')
                    ->all();

                $result = [];

                foreach ($models as $model) {
                    if (array_key_exists($model['id'], $brandAccounts)) {
                        $brandAccount = $brandAccounts[$model['id']];
                        $brandAccount->brandTitle = $model['title'];
                        $result[] = $brandAccount;
                    } else {
                        $result[] = new BrandAccount([
                            'brand_id' => $model['id'],
                            'shop_id' => $shop->id,
                            'brandTitle' => $model['title']
                        ]);
                    }
                }

                return $result;
            }
        ]);

        return $dataProvider;
    }
}