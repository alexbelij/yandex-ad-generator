<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 30.09.16
 * Time: 18:37
 */

namespace app\lib\tasks;

use app\lib\Logger;
use app\models\Ad;
use app\models\Product;
use app\models\TaskQueue;

/**
 * Метод удаления дубликатов объявлений
 *
 * Class AdDuplicateDeleteTask
 * @package app\lib\tasks
 */
class AdDuplicateDeleteTask extends AbstractTask
{
    const TASK_NAME = 'adDuplicateDelete';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->logger = new Logger();
    }


    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $shop = $this->task->shop;

        $query = Product::find()->andWhere(['shop_id' => $shop->primaryKey]);
        $this->logger->log('Start duplicate delete...');

        $countDeletedAd = 0;
        $countDeletedPhrases = 0;
        $productsCount = 0;
        $adsCount = 0;
        $countAdToSuspend = 0;

        /** @var Product[] $products */
        foreach ($query->batch(1000) as $products) {
            foreach ($products as $product) {
                $uniquePhrases = [];
                $this->logger->log("remove duplicate for product {$product->id} - {$product->title}");
                /** @var Ad[] $ads */
                $ads = Ad::find()
                    ->andWhere([
                        'product_id' => $product->id,
                    ])
                    ->orderBy(['is_auto' => SORT_ASC, 'id' => SORT_ASC])
                    ->all();
                if (count($ads) <= 1) {
                    continue;
                }
                $productsCount++;
                $countRemovedPhrasesPerProduct = 0;
                foreach ($ads as $ad) {
                    $adsCount++;
                    $keywords = preg_split("#(\r\n|\n)#", $ad->keywords);
                    $isChanged = false;
                    foreach ($keywords as $key => $keyword) {
                        $matchKeyword = $this->prepareKeyword($keyword);
                        $this->logger->log($matchKeyword);
                        if (in_array($matchKeyword, $uniquePhrases)) {
                            unset($keywords[$key]);
                            $isChanged = true;
                            $countDeletedPhrases++;
                            $countRemovedPhrasesPerProduct++;
                        } else {
                            $uniquePhrases[] = $matchKeyword;
                        }
                    }
                    if ($isChanged && $ad->is_auto) {
                        if (empty($keywords) && $ad->isPublished()) {
                            $ad->markForDelete();
                            $countDeletedAd++;
                            $countAdToSuspend++;
                        } elseif (empty($keywords)) {
                            $ad->delete();
                            $countDeletedAd++;
                        } else {
                            $ad->keywords = implode("\r\n", $keywords);
                            $ad->save();
                        }
                    }
                }
                $this->logger->log('count deleted duplicates for product: ' . $countRemovedPhrasesPerProduct);
            }
        }
        $this->logger->log('Total products count: ' . $productsCount);
        $this->logger->log('Total ads count: ' . $adsCount);
        $this->logger->log('Total remove phrases: ' . $countDeletedPhrases);
        $this->logger->log('Total remove ads: ' . $countDeletedAd);

        if ($countAdToSuspend && !TaskQueue::hasActiveTasks($this->task->shop_id, DeleteAdTask::TASK_NAME)) {
            TaskQueue::createNewTask($this->task->shop_id, DeleteAdTask::TASK_NAME);
            $this->logger->log('Create delete ad task');
        }
    }

    /**
     * @param string $keyword
     * @return string
     */
    protected function prepareKeyword($keyword)
    {
        return preg_replace('#[^\w\s]#u', '', mb_strtolower($keyword));
    }
}
