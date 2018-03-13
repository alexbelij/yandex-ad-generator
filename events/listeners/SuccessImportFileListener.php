<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.10.16
 * Time: 19:57
 */

namespace app\events\listeners;

use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\gateways\cached\BrandsCachedGateway;
use app\lib\api\shop\gateways\cached\CategoriesCachedGateway;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\models\Shop;
use yii\base\Event;
use yii\base\Object;

/**
 * Class SuccessImportFileHandler
 * @package app\events\handlers
 */
class SuccessImportFileListener extends Object implements EventListenerInterface
{
    const SUCCESS_FILE_IMPORT = 'success_file_import';

    /**
     * @inheritDoc
     */
    public function getEvents()
    {
        return [
            self::SUCCESS_FILE_IMPORT => [
                'onSuccessFileImport'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function onSuccessFileImport(Event $event)
    {
        /** @var Shop $shop */
        $shop = $event->sender;

        $brandsGateway = new BrandsCachedGateway(BrandsGateway::factory($shop));
        $categoriesGateway = new CategoriesCachedGateway(CategoriesGateway::factory($shop));

        $brandsGateway->clearCacheList();
        $categoriesGateway->clearCacheList();
    }
}
