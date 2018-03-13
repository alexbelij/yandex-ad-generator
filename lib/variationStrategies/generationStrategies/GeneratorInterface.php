<?php

namespace app\lib\variationStrategies\generationStrategies;

use app\lib\dto\GenerationInfoDto;

/**
 * Interface GeneratorInterface
 * @package app\lib\variationStrategies\generationStrategies
 */
interface GeneratorInterface
{
    /**
     * @param GenerationInfoDto $dto
     * @return string[]
     */
    public function generate(GenerationInfoDto $dto);
}
