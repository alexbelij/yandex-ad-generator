<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:28
 */

namespace app\lib\api\shop\query\translator;

use app\models\ExternalCategory;
use yii\db\ActiveQuery;

/**
 * Class CategoryTranslator
 * @package app\lib\api\shop\query\translator
 */
class CategoryTranslator extends AbstractInternalTranslator
{
    /**
     * @inheritDoc
     */
    protected function getQuery()
    {
        return ExternalCategory::find();
    }

    /**
     * @inheritDoc
     */
    public function translate(array $params)
    {
        /** @var ActiveQuery $query */
        $query = parent::translate($params);

        $query->leftJoin(['ex' => ExternalCategory::tableName()], 'ex.outer_id = {{%external_category}}.parent_id AND ex.shop_id = {{%external_category}}.shop_id');
        $query->select('{{%external_category}}.*, ex.id as parent_id');

        if (!empty($params['title'])) {
            $query->andWhere(['LIKE', ExternalCategory::tableName() . '.title', $params['title']]);
        }

        if (!empty($params['parent_id'])) {
            $query->andWhere(['parent_id' => $params['parent_id']]);
        }

        if (!empty($params['order'])) {
            $query->orderBy($params['order']);
        }

        return $query;
    }
}
