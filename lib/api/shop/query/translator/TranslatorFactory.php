<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:11
 */

namespace app\lib\api\shop\query\translator;

use app\lib\api\shop\dataSource\ApiDataSource;
use app\lib\api\shop\dataSource\DataSourceInterface;
use app\lib\api\shop\dataSource\InternalDataSource;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use yii\base\Exception;

/**
 * Class TranslatorFactory
 * @package app\lib\api\shop\query\translator
 */
class TranslatorFactory
{
    /**
     * @param DataSourceInterface $dataSource
     * @return TranslatorInterface
     * @throws Exception
     */
    public static function create(DataSourceInterface $dataSource)
    {
        if ($dataSource instanceof ApiDataSource) {
            return new ApiTranslator($dataSource->getShop());
        } elseif ($dataSource instanceof InternalDataSource) {

            switch ($dataSource->getModelClass()) {
                case ExternalBrand::className():
                    return new BrandTranslator($dataSource->getShop());
                case ExternalCategory::className():
                    return new CategoryTranslator($dataSource->getShop());
                case ExternalProduct::className():
                    return new ProductTranslator($dataSource->getShop());
            }

            throw new Exception('Не удалось определить транслятор запроса');
        }

        throw new Exception('Неизвестный источник данных');
    }
}
