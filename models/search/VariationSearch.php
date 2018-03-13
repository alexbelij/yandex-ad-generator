<?php
namespace app\models\search;

use app\lib\api\shop\gateways\ApiDataSource;
use app\lib\api\shop\query\BaseQuery;
use app\lib\provider\ApiDataProvider;
use app\lib\provider\processStrategies\VariationAfterLoadStrategy;
use app\models\Shop;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class VariationSearch
 * @package app\models\search
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
abstract class VariationSearch extends Model
{
    /**
     * @var int
     */
    public $shopId;

    /**
     * @var mixed
     */
    public $ids;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $onlyActive = true;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['shopId', 'ids', 'name', 'onlyActive'], 'safe']
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'onlyActive' => 'Только активные'
        ];
    }


    /**
     * @param array $params
     * @return ApiDataProvider
     */
    public function search($params = [])
    {
        $this->load($params);
        
        if (!$this->shopId) {
            throw new \InvalidArgumentException('ShopId is empty');
        }
        
        $shop = Shop::findOne($this->shopId);
        
        if (!$shop) {
            throw new \InvalidArgumentException('Shop not found');
        }
        
        return new ApiDataProvider([
            'gateway' => $this->getGateway($shop),
            'query' => $this->getQuery(),
            'processStrategy' => new VariationAfterLoadStrategy([
                'shopId' => $this->shopId,
                'type' => $this->getType()
            ])
        ]);
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param Shop $shop
     * @return ApiDataSource
     */
    abstract public function getGateway(Shop $shop);

    /**
     * @return BaseQuery
     */
    abstract public function getQuery();
}
