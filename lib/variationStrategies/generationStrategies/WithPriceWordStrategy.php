<?php

namespace app\lib\variationStrategies\generationStrategies;

use app\lib\dto\GenerationInfoDto;

/**
 * Class TitleVariantsStrategy
 * @package app\lib\variationStrategies\generationStrategies
 */
class WithPriceWordStrategy implements GeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate(GenerationInfoDto $dto)
    {
        $variations = [];
        foreach (['цена', 'купить'] as $saleWord) {
            $variations[] = "{$dto->brandTitle} {$dto->productTitle} $saleWord";
        }

        return $variations;
    }
}
