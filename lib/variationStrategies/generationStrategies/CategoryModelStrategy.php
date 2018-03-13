<?php

namespace app\lib\variationStrategies\generationStrategies;

use app\lib\dto\GenerationInfoDto;

/**
 * Class TitleVariantsStrategy
 * @package app\lib\variationStrategies\generationStrategies
 */
class CategoryModelStrategy implements GeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate(GenerationInfoDto $dto)
    {
        $variations = [];
        foreach ($dto->categories as $categoryTitle) {
            $categoryTitle = str_replace('-', ' ', $categoryTitle);
            $variations[] = "$categoryTitle $dto->productTitle";
        }

        return $variations;
    }
}
