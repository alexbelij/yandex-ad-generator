<?php

namespace app\lib\import\yml\strategies\categoryBrandStrategy;

use app\lib\import\yml\strategies\entity\Offer;
use app\models\ExternalBrand;
use app\models\ExternalCategory;

/**
 * Class OfferTagParser
 * @package app\lib\import\yml\strategies\categoryBrandStrategy
 */
class OfferTagParser extends \app\lib\import\yml\strategies\defaultStrategy\OfferTagParser
{
    /**
     * @inheritDoc
     */
    protected function getExternalBrand(Offer $offer)
    {
        $extCategory = parent::getExternalCategory($offer);

        $extBrand = ExternalBrand::find()
            ->andWhere([
                'shop_id' => $this->externalProduct->shop_id,
                'title' => $extCategory->title
            ])
            ->one();

        //Бренд определен
        if (!$extBrand) {
            $extBrand = ExternalBrand::getDefaultBrand($this->externalProduct->shop_id);
        }

        return $extBrand;
    }

    /**
     * @inheritDoc
     */
    protected function getExternalCategory(Offer $offer)
    {
        $extCategory = parent::getExternalCategory($offer);

        return $extCategory->parent ?: $extCategory;
    }
}
