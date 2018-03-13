<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:26
 */

namespace app\lib\api\shop\query\translator;

use app\models\ExternalBrand;

/**
 * Class BrandTranslator
 * @package app\lib\api\shop\query\translator
 */
class BrandTranslator extends AbstractInternalTranslator
{
    /**
     * @inheritDoc
     */
    protected function getQuery()
    {
        return ExternalBrand::find();
    }

    /**
     * @inheritDoc
     */
    public function translate(array $params)
    {
        $query = parent::translate($params);

        $query->andWhere(['is_deleted' => false]);

        if (!empty($params['title'])) {
            $query->andWhere(['LIKE', ExternalBrand::tableName() . '.title', $params['title']]);
        }

        $query->orderBy('title');

        return $query;
    }
}
