<?php

namespace app\lib\variationStrategies\generationStrategies;

use app\lib\dto\GenerationInfoDto;

/**
 * Class RotationStrategy
 * @package app\lib\variationStrategies\generationStrategies
 */
class RotationStrategy implements GeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate(GenerationInfoDto $dto)
    {
        $variations = [];
        foreach ($dto->categories as $categoryTitle) {
            $categoryTitle = str_replace('-', ' ', $categoryTitle);
            foreach ($dto->rotationTitles as $title) {
                $variations[] = "$categoryTitle {$dto->brandTitle} $title";
                $variations[] = "{$dto->brandTitle} $title";
            }
        }

        return $variations;
    }
}
