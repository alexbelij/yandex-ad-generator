<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.10.16
 * Time: 17:43
 */

namespace app\lib\tasks;
use app\helpers\ArrayHelper;
use app\helpers\JsonHelper;
use app\helpers\StringHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\variationStrategies\DefaultStrategy;
use app\models\Ad;
use app\models\TaskQueue;
use app\models\Variation;

/**
 * Class DeleteAdWithoutVariationTask
 * @package app\lib\tasks
 */
class DeleteAdWithoutVariationTask extends AbstractTask
{
    const TASK_NAME = 'deleteAdWithoutVariation';


    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        /** @var BrandsGateway $brandsGateway */
        $brandsGateway = BrandsGateway::factory($this->task->shop);
        $brandsList = $brandsGateway->getBrandsList();
        $brandTitles = ArrayHelper::getColumn($brandsList, 'title');
        $adQuery = Ad::find()
            ->innerJoinWith('product')
            ->andWhere([
                '{{%product}}.shop_id' => $this->task->shop_id,
                'is_auto' => 1
            ]);

        $brandVariationsList = Variation::find()
            ->select('variation')
            ->andWhere([
                'entity_type' => Variation::TYPE_BRAND,
                'shop_id' => $this->task->shop_id
            ])->column();

        foreach ($brandVariationsList as $brandVariation) {
            $brandVariations = StringHelper::explodeByDelimiter($brandVariation);
            foreach ($brandVariations as $variation) {
                $brandTitles[] = $variation;
            }
        }
        $brandTitles = array_filter(array_map('trim', array_unique($brandTitles)));

        $this->getLogger()->log('Объявлений для проверки: ' . $adQuery->count());
        $adToDeleteCount = 0;
        /** @var Ad[] $ads */
        foreach ($adQuery->batch(1000) as $ads) {
            foreach ($ads as $ad) {
                if ($ad->title == DefaultStrategy::LIMIT_REACH_MESSAGE) {
                    continue;
                }
                $this->getLogger()->log("Проверка объявления - id: {$ad->id}, title: {$ad->title}");
                $hasMatch = false;
                foreach ($brandTitles as $brandTitle) {
                    if (preg_match("#{$brandTitle}#iu", $ad->title)) {
                        $this->getLogger()->log('Совпадение: ' . $brandTitle);
                        $hasMatch = true;
                        break;
                    }
                }
                if (!$hasMatch) {
                    $adToDeleteCount++;
                    $this->getLogger()->log("Помечаем объявление для удаления: " . JsonHelper::encodeModelPretty($ad));
                    $ad->markForDelete();
                }
            }
        }

        $this->getLogger()->log("======================================================");
        $this->getLogger()->log("Количество объявлений для удаления: $adToDeleteCount");
        $this->getLogger()->log("======================================================");

        if ($adToDeleteCount && !TaskQueue::hasActiveTasks($this->task->shop_id, DeleteAdTask::TASK_NAME)) {
            TaskQueue::createNewTask($this->task->shop_id, DeleteAdTask::TASK_NAME);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'delete_ad_' . $this->task->id;
    }
}
