<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:22
 */

namespace app\lib\api\shop\query\translator;

use app\models\Shop;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AbstractInternalTranslator
 * @package app\lib\api\shop\query\translator
 */
abstract class AbstractInternalTranslator implements TranslatorInterface
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * AbstractInternalTranslator constructor.
     * @param Shop $shop
     */
    public function __construct(Shop $shop = null)
    {
        $this->shop = $shop;
    }

    /**
     * @param array $params
     * @return ActiveQuery
     */
    public function translate(array $params)
    {
        $query = $this->getQuery();
        /** @var ActiveRecord $modelClass */
        $modelClass = $query->modelClass;

        if (!empty($params['limit'])) {
            $query->limit($params['limit']);
        }

        if (!empty($params['offset'])) {
            $query->offset($params['offset']);
        }

        if (isset($params['page'])) {
            $offset = $query->limit * ($params['page'] - 1);
            $query->offset($offset);
        }

        if (!empty($params['ids'])) {
            $ids = is_string($params['ids']) ? explode(',', $params['ids']) : $params['ids'];
            $query->andWhere([$this->getIdField() => $ids]);
        }

        if ($this->shop) {
            $query->andWhere([$modelClass::tableName() . '.shop_id' => $this->shop->id]);
        }

        return $query;
    }

    /**
     * @return string
     */
    protected function getIdField()
    {
        $modelClass = $this->getQuery()->modelClass;
        return $modelClass::tableName() . '.id';
    }

    /**
     * @return ActiveQuery
     */
    abstract protected function getQuery();
}
