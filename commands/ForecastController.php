<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.11.16
 * Time: 19:45
 */

namespace app\commands;

use app\components\FileLogger;
use app\lib\services\PointsForecast;
use app\models\GeneratorSettings;
use app\models\Shop;
use yii\console\Controller;

/**
 * Class ForecastController
 * @package app\commands
 */
class ForecastController extends Controller
{
    /**
     * Обновление прогноза расчета баллов
     *
     * @param null $shopId
     */
    public function actionUpdate($shopId = null)
    {
        $logger = new FileLogger('forecast_update' . date('Y.m.d_H:i'));
        /** @var Shop[] $shops */
        $shops = Shop::find()
            ->andFilterWhere(['id' => $shopId])
            ->all();

        foreach ($shops as $shop) {
            $generatorSettings = GeneratorSettings::forShop($shop->id);
            if (!$generatorSettings) {
                continue;
            }
            $logger->log("Обновление прогноза для магазина {$shop->id}, {$shop->name}");
            $pointsForecast = new PointsForecast();
            try {
                $pointsForecast->update($shop);
                $logger->log('Прогноз обновлен');
            } catch (\Exception $e) {
                $logger->log('Возникли ошибки: ' . $e->getMessage());
            }
        }
    }
}
