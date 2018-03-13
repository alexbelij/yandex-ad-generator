<?php

namespace app\lib\variationStrategies;

use app\helpers\StringHelper;
use app\lib\api\shop\models\ExtProduct;
use app\lib\dto\GenerationInfoDto;
use app\lib\dto\Template;
use app\lib\variationStrategies\generationStrategies\BaseGenerator;
use app\lib\variationStrategies\generationStrategies\GeneratorInterface;
use app\lib\variationStrategies\generationStrategies\RotationStrategy;
use app\lib\variationStrategies\generationStrategies\TitleVariantsStrategy;
use app\lib\variationStrategies\generationStrategies\WithPriceWordStrategy;
use app\models\Ad;
use app\models\Shop;
use app\models\AdTemplate;
use app\models\Variation;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class DefaultStrategy
 * @package app\lib\variationStrategies
 */
class DefaultStrategy implements VariationStrategyInterface
{
    const LIMIT_WORD_COUNT = 7;
    const LIMIT_REACH_MESSAGE = 'Не хватило места';
    const LIMIT_WORD_LENGTH = 35;
    const MIN_WORD_LENGTH = 3;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var array
     */
    protected $variations = [];

    /**
     * @var array
     */
    protected $generationStrategies = [
        TitleVariantsStrategy::class,
        WithPriceWordStrategy::class,
        RotationStrategy::class,
    ];

    /**
     * VariationGeneratorService constructor.
     * @param Shop $shop
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Генерация тайтла и ключевых слов в зависимости от количества вариаций бренда
     *
     * @param ExtProduct $product
     * @return array
     */
    public function generate(ExtProduct $product)
    {
        $allKeywords = $this->generateKeywords($product);
        $templates = $this->generateTemplates($product);
        $result = [];
        for ($i = 0; $i < count($templates); $i++) {
            $result[] = [
                'keywords' => ArrayHelper::getValue($allKeywords, $i, []),
                'template' => ArrayHelper::getValue($templates, $i)
            ];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addVariationsFromAds(array $ads)
    {
        foreach ($ads as $ad) {
            foreach (StringHelper::explodeByDelimiter($ad->keywords) as $keyword) {
                $this->addVariation($keyword);
            }
        }
    }

    /**
     * @param string $variation
     * @return $this
     */
    public function addVariation($variation)
    {
        $this->variations[] = $this->cleanVariation(mb_strtolower($variation));
        return $this;
    }

    /**
     * @param ExtProduct $product
     * @return bool
     */
    protected function beforeGenerateKeywords(ExtProduct $product)
    {
        return $this->isValid($this->cleanVariation($product->title));
    }

    /**
     * @param array $variations
     * @return array|string
     */
    protected function afterGenerateVariations(array $variations)
    {
        $result = [];
        foreach ($variations as $i => $variation) {
            if (mb_strlen($variation) < 3) {
                continue;
            }
            $words = explode(' ', $variation);
            foreach ($words as $key => $word) {
                if (mb_strlen($word) < 3 || preg_match('#[\d.]#', $word)) {
                    $words[$key] = '+' . $word;
                }
            }
            $result[] = implode(' ', $words);
        }

        return $result;
    }

    /**
     * @param ExtProduct $product
     * @return array
     * @throws Exception
     */
    public function generateKeywords(ExtProduct $product)
    {
        $productTitle = $this->cleanVariation($product->title);

        if (!$this->beforeGenerateKeywords($product)) {
            return [];
        }

        $titleVariants = [];
        $titleVariants[] = $productTitle;

        $aParts = implode(' ', $this->extractParts($productTitle)); //раздельно
        if ($aParts != $productTitle) {
            $titleVariants[] = $aParts;
        }

        $inRotationTitles = [];
        foreach ($titleVariants as $title) {
            $inRotationTitles = array_merge($inRotationTitles, explode(' ', $title));
        }

        $titleParts = explode(' ', $productTitle);
        while (array_shift($titleParts)) {
            $inRotationTitles[] = implode(' ', $titleParts);
        }

        $inRotationTitles = array_filter(array_values(array_unique($inRotationTitles)));

        $titleParts = array_values(array_filter(explode(' ', $productTitle)));
        $pairVariants = [];
        $titlePartsCount = count($titleParts);
        for ($i = 0; $i < $titlePartsCount - 1; $i++) {
            $prefix = implode(' ', array_slice($titleParts, 0, $i + 1));
            $prefix2 = $titleParts[$i];
            for ($j = $i + 1; $j < $titlePartsCount; $j++) {
                $pairVariants[] = $prefix . ' ' . $titleParts[$j];
                $pairVariants[] = $prefix2 . ' ' . $titleParts[$j];
            }
        }

        $pairVariants = array_values(array_unique($pairVariants));

        if (strpos($productTitle, ' ') !== false && $this->isCanGlue($titleParts)) {
            $titleVariants[] = str_replace(' ', '', $productTitle); // слитно
        }

        for ($i = 0; $i < $titlePartsCount; $i++) {
            $startVariations = array_slice($titleParts, 0, $i + 1);
            $endVariations = array_slice($titleParts, $i + 1);

            $startSep = ' ';
            if ($this->isCanGlue($startVariations)) {
                $startSep = '';
            }

            $endSep = ' ';
            if ($this->isCanGlue($endVariations)) {
                $endSep = '';
            }

            $pairVariants[] = implode($startSep, $startVariations) . ' ' . implode(' ', $endVariations);
            $pairVariants[] = implode(' ', $startVariations) . ' ' . implode($endSep, $endVariations);
        }

        $titleVariants = array_filter($titleVariants, [$this, 'isValid']);

        if (empty($titleVariants)) {
            return [];
        }

        $inRotationTitles = array_merge($inRotationTitles, $pairVariants);
        $inRotationTitles = array_filter($inRotationTitles, [$this, 'isValid']);

        $generationInfoDto = new GenerationInfoDto([
            'productTitle' => $productTitle,
            'rotationTitles' => $inRotationTitles,
            'titleVariants' => $titleVariants,
            'categories' => $this->getCategoryVariations($product)
        ]);

        $result = [];

        foreach ($this->getBrandVariations($product) as $brandTitle) {
            $brandTitle = str_replace('-', ' ', $brandTitle);
            $generationInfoDto->brandTitle = $brandTitle;

            $variations = [];
            foreach ($this->runGeneratorStrategies($generationInfoDto) as $variation) {
                $variation = $this->cleanVariation($variation);
                $variations[] = $this->filterKeywordByLimit($variation);
            }

            $variations = $this->uniqueVariations($variations);
            $variations = $this->afterGenerateVariations($variations);
            $result[] = $variations;
        }

        return $result;
    }

    /**
     * @param GenerationInfoDto $dto
     * @return array
     */
    protected function runGeneratorStrategies(GenerationInfoDto $dto)
    {
        $variations = [];
        foreach ($this->generationStrategies as $strategyClass) {
            /** @var GeneratorInterface $strategy */
            $strategy = new $strategyClass;
            $variations = array_merge($variations, $strategy->generate($dto));
        }

        return $variations;
    }

    /**
     * Возвращает true, если есть склейка слов, даже несмотря на наличие вариации с цифрами
     *
     * @param array $variations
     * @return bool
     */
    protected function isCanGlue(array $variations)
    {
        $withoutNumbers = 0;
        foreach ($variations as $variation) {
            if (!preg_match('#\d#', $variation)) {
                $withoutNumbers++;
            }
        }

        //если присутствуют вариации без цифр больше одной, то не склеиваем
        return $withoutNumbers < 2;
    }

    /**
     * @param string $variation
     * @return bool
     */
    protected function isValid($variation)
    {
        $isValidByLength = mb_strlen($variation) >= self::MIN_WORD_LENGTH;

        return $isValidByLength
            || (preg_match('#\d#', $variation) && preg_match('#[a-zA-Zа-яА-Я]#u', $variation));
    }

    /**
     * Удаление лишних символов
     *
     * @param string $variation
     * @return mixed
     */
    protected function cleanVariation($variation)
    {
        $variation = preg_replace('#[^\w.]#u', ' ', $variation);
        return preg_replace('#(\s+|_)#', ' ', $variation);
    }

    /**
     * Фильтрация фразы согласно ограничению яндекса на кол-во слов, точка считается разделителем
     *
     * @param string $keyPhrase
     * @return array
     */
    protected function filterKeywordByLimit($keyPhrase)
    {
        $delimiterCount = 0;
        $parts = [];
        $word = '';

        $words = array_filter(
            explode(' ', $keyPhrase),
            function ($word) {
                return mb_strlen($word) <= self::LIMIT_WORD_LENGTH;
            }
        );
        $keyPhrase = implode(' ', $words);

        $phraseLen = mb_strlen($keyPhrase);
        for ($i = 0; $i < $phraseLen; $i++) {
            $ch = mb_substr($keyPhrase, $i, 1);
            if ($ch == ' ') {
                $delimiterCount++;
                $parts[] = $word;
                $word = '';
                continue;
            }

            if ($ch == '.') {
                $delimiterCount++;
            }

            if ($delimiterCount >= self::LIMIT_WORD_COUNT) {
                break;
            }

            $word .= $ch;

            if ($i == $phraseLen - 1) {
                $parts[] = $word;
            }
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * Оставляем только уникальные вариации
     *
     * @param array $variations
     * @return array
     */
    protected function uniqueVariations(array $variations)
    {
        $result = [];
        foreach ($variations as $variation) {
            $variation = trim($variation);
            $testVariation = mb_strtolower($variation);
            if (!$variation || in_array($testVariation, $this->variations)) {
                continue;
            }
            $this->variations[] = $testVariation;
            $result[] = $variation;
        }

        return $result;
    }

    /**
     * Разбивает заголовок на отдельные части, например AVR300B5 => [AVR 300 B 5]
     *
     * @param string $title
     * @return array
     */
    protected function extractParts($title)
    {
        $previous = null;
        $word = '';
        $parts = [];
        $count = mb_strlen($title);
        for ($i = 0; $i < $count; $i++) {
            $char = mb_substr($title, $i, 1);
            if ($previous !== null) {
                if (preg_match('#([^\w])#u', $char) || preg_match('#([^\w])#u', $previous)) {
                    $word .= $char;
                } elseif (ctype_digit($char) && ctype_digit($previous)) {
                    $word .= $char;
                } elseif (!ctype_digit($char) && !ctype_digit($previous)) {
                    $word .= $char;
                } else {
                    $parts[] = $word;
                    $word = $char;
                }
            } else {
                $word .= $char;
            }

            if ($i == ($count - 1)) {
                $parts[] = $word;
            }

            $previous = $char;
        }

        return $parts;
    }

    /**
     * @param ExtProduct $product
     * @return Template[]
     */
    protected function generateTemplates(ExtProduct $product)
    {
        $templates = [];
        foreach ($this->getBrandVariations($product) as $brandTitle) {
            $templates[] = $this->findMatchTitle($product, $brandTitle);
        }

        return $templates;
    }

    /**
     * @param ExtProduct $product
     * @param string $brandTitle
     * @return Template
     */
    protected function findMatchTitle(ExtProduct $product, $brandTitle)
    {
        foreach ($this->getCategoryVariations($product) as $categoryTitle) {
            foreach ($this->getTemplates($product, $categoryTitle, $brandTitle) as $template) {
                if (mb_strlen($template->title) <= AdTemplate::TITLE_MAX_SIZE) {
                    return $template;
                }
            }
        }

        return new Template([
            'title' => self::LIMIT_REACH_MESSAGE,
        ]);
    }

    /**
     * @param ExtProduct $product
     * @param $categoryTitle
     * @param $brandTitle
     * @return Template[]
     */
    protected function getTemplates(ExtProduct $product, $categoryTitle, $brandTitle)
    {
        $placeholders = [
            '[brand]' => $brandTitle,
            '[category]' => $categoryTitle,
            '[price]' => $product->price,
            '[title]' => $product->title,
            '[extTitle]' => $product->extTitle
        ];

        $templateTitles = [];

        foreach ($this->getAllAvailableTemplates() as $template) {
            if ($this->isValidTemplate($template, $product)) {
                $templateTitles[] = new Template([
                    'title' => preg_replace('#\s+#', ' ', preg_replace('#[^\w.\s-!/]#u', ' ', strtr($template->title, $placeholders))),
                    'templateId' => $template->id
                ]);
            }
        }

        return $templateTitles;
    }

    /**
     * @param AdTemplate $template
     * @param ExtProduct $product
     * @return bool
     */
    protected function isValidTemplate(AdTemplate $template, ExtProduct $product)
    {
        return (
                in_array($product->getBrandId(), $template->getBrandIds()) ||
                in_array($product->getCategoryId(), $template->getCategoryIds())
            ) && $product->price >= $template->price_from && $product->price <= $template->price_to;
    }

    /**
     * @return AdTemplate[]
     */
    protected function getAllAvailableTemplates()
    {
        static $cache = [];

        if (empty($cache[$this->shop->id])) {
            $cache[$this->shop->id] = AdTemplate::find()
                ->andWhere(['shop_id' => $this->shop->id])
                ->orderBy('sort')
                ->all();
        }

        return $cache[$this->shop->id];
    }

    /**
     * @param ExtProduct $product
     * @return array
     */
    public function getCategoryVariations(ExtProduct $product)
    {
        static $cache = [];

        $categoryId = $product->getCategoryId();
        $cacheKey = $categoryId . '_' . $product->typePrefix;
        if (!array_key_exists($cacheKey, $cache)) {
            /** @var Variation $categoryVariation */
            $categoryVariation = Variation::find()
                ->andWhere([
                    'shop_id' => $this->shop->id,
                    'entity_type' => Variation::TYPE_CATEGORY,
                    'entity_id' => $categoryId
                ])->one();

            $variations = [];
            if ($categoryVariation) {
                $variations = array_filter($categoryVariation->getVariationList(true));
            } elseif (empty($product->typePrefix)) {
                $variations = [ArrayHelper::getValue($product, 'categories.0.title')];
            }

            if ($product->typePrefix) {
                $variations[] = $product->typePrefix;
            }

            $cache[$cacheKey] = $variations;
        }

        return $cache[$cacheKey];
    }

    /**
     * @param ExtProduct $product
     * @return array
     */
    public function getBrandVariations(ExtProduct $product)
    {
        static $cache = [];
        $brandId = $product->getBrandId();
        if (!array_key_exists($brandId, $cache)) {
            /** @var Variation $brandVariation */
            $brandVariation = Variation::find()
                ->andWhere([
                    'shop_id' => $this->shop->id,
                    'entity_type' => Variation::TYPE_BRAND,
                    'entity_id' => $brandId
                ])->one();
            if ($brandVariation) {
                $variations = $brandVariation->getVariationList(true);
            } else {
                $variations = [ArrayHelper::getValue($product, 'brand.title')];
            }

            $cache[$brandId] = $variations;
        }

        return $cache[$brandId];
    }

    /**
     * @param Ad $ad
     * @param ExtProduct $extProduct
     * @return bool
     */
    public function adHasCurrentBrands(Ad $ad, ExtProduct $extProduct)
    {
        $brandVariations = $this->getBrandVariations($extProduct);
        if ($ad->title == self::LIMIT_REACH_MESSAGE) {
            return true;
        }

        foreach ($brandVariations as $brandVariation) {
            if (mb_stripos($ad->title, $brandVariation) !== false) {
                return true;
            }
            if (mb_stripos($ad->keywords, $brandVariation) !== false) {
                return true;
            }
        }

        return false;
    }
}