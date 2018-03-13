<?php
namespace app\lib\provider\processStrategies;

use app\models\Variation;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class VariationStrategy
 * @package app\lib\provider\loadStrategies
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class VariationAfterLoadStrategy extends Object implements AfterLoadProcessStrategy
{
    /**
     * @var int
     */
    public $shopId;

    /**
     * @var string
     */
    public $type;
    
    /**
     * @inheritDoc
     */
    public function process($models)
    {
        if (empty($models)) {
            return [];
        }

        $variationModels = Variation::find()
            ->distinct()
            ->joinWith(['variationItems'])
            ->andWhere([
                'shop_id' => $this->shopId,
                'entity_type' => $this->type,
                'entity_id' => ArrayHelper::getColumn($models, 'id')
            ])
            ->all();

        $variationModels = ArrayHelper::index($variationModels, null, 'entity_id');
        $result = [];
        foreach ($models as $model) {
            if (array_key_exists($model['id'], $variationModels)) {
                $items = $variationModels[$model['id']];
            } else {
                $items = [new Variation([
                    'entity_type' => $this->type,
                    'entity_id' => $model['id'],
                    'shop_id' => $this->shopId
                ])];
            }
            foreach ($items as $item) {
                $item->title = $model['title'];
                $result[] = $item;
            }
        }

        return $result;
    }
}
