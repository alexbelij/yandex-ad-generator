<?php

namespace app\lib\import\yml\strategies\defaultStrategy;

use app\components\LoggerInterface;
use app\components\PhpMorphy;
use app\helpers\ArrayHelper;
use app\helpers\StringHelper;
use app\lib\import\yml\AbstractTagParser;
use app\lib\import\yml\CategoriesTree;
use app\lib\import\yml\extensions\ColorsFromOfferExtension;
use app\lib\import\yml\extensions\ExtensionInterface;
use app\lib\import\yml\extensions\ExtensionItemDto;
use app\lib\import\yml\extensions\WordExceptionSaver;
use app\lib\import\yml\strategies\entity\Offer;
use app\lib\import\yml\strategies\mappers\OfferMapper;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use app\models\FileImport;
use app\models\Shop;
use app\models\Variation;
use app\models\WordException;

/**
 * Class OfferTag
 * @package app\lib\import\yml
 */
class OfferTagParser extends AbstractTagParser
{
    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var ExternalProduct
     */
    protected $externalProduct;

    /**
     * @var string
     */
    private $data = '';

    /**
     * @var array
     */
    private static $brandList;

    /**
     * @var CategoriesTree
     */
    protected $categoriesTree;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var WordExceptionSaver
     */
    protected static $wordExceptionSaver;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @inheritDoc
     */
    public function __construct(FileImport $fileImport, LoggerInterface $logger, array $categories = [])
    {
        parent::__construct($fileImport, $logger);
        $this->categoriesTree = new CategoriesTree($categories);
        $this->shop = $this->fileImport->shop;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param array $extensions
     * @return OfferTagParser
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parseAttributes($tagName, $attributes)
    {
        if ($tagName == 'offer') {

            $this->attributes = [
                'id' => trim($attributes['ID']),
                'available' => !empty($attributes['AVAILABLE']) && $attributes['AVAILABLE'] != 'false',
                'type' => ArrayHelper::getValue($attributes, 'TYPE')
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function parseCharacters($tagName, $data)
    {
        if (!empty($data)) {
            $this->data .= $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function end($tagName)
    {
        $this->data = trim($this->data);
        if ($tagName == 'offer') {

            $offer = $this->getOffer();
            $this->runExtensions($offer);

            $this->externalProduct = $this->getExternalProduct($offer);
            if ($this->externalProduct->is_manual) {
                $this->externalProduct->save();
                return;
            }

            $brand = $this->getExternalBrand($offer);
            $this->externalProduct->brand_id = $brand->primaryKey;

            $category = $this->getExternalCategory($offer);
            $this->externalProduct->category_id = $category->primaryKey;

            $this->externalProduct->original_title = $offer->name;

            if ($brand->isUnknown()) {
                $brandInfo = $this->matchBrandByTitle($this->externalProduct->title);
                if ($brandInfo) {
                    $this->externalProduct->brand_id = $brandInfo['id'];
                }
            }

            $this->externalProduct->setFileImportId($this->fileImport->primaryKey);

            if ($brand->id != $this->externalProduct->brand_id) {
                $brand = ExternalBrand::findOne($this->externalProduct->brand_id);
            }

            $productTitle = $offer->model ?: $offer->name;
            if (!$category->isUnknown() && !$brand->isUnknown()) {
                $this->externalProduct->title = $this->getProductModelFromTitle($productTitle);
            } else {
                $this->externalProduct->title = $productTitle;
            }

            $res = $this->externalProduct->save();

            $this->logger->log("Finish parse offer with outer_id: {$this->externalProduct->outer_id} is " .
                ($res ? "success" : "fail"));

            if (!$res) {
                $this->logger->log("Errors: " . json_encode($this->externalProduct->getFirstErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            $this->logger->log("Details:" . json_encode($this->externalProduct->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $this->getWordExceptionSaver()->save();

        } else {
            $this->attributes[$tagName] = $this->data;
        }

        $this->data = '';
    }

    /**
     * @return Offer|mixed
     */
    protected function getOffer()
    {
        return (new OfferMapper())->map($this->attributes);
    }

    /**
     * Запуск плагинов
     *
     * @param Offer $offer
     */
    protected function runExtensions(Offer $offer)
    {
        $offerItemDto = new ExtensionItemDto([
            'data' => $offer,
            'extra' => [
                'saver' => $this->getWordExceptionSaver()
            ]
        ]);
        foreach ($this->extensions as $extensionClass) {
            /** @var ExtensionInterface $extension */
            $extension = new $extensionClass;
            $extension->run($offerItemDto);
        }
    }

    /**
     * @param Offer $offer
     * @return ExternalCategory
     */
    protected function getExternalCategory(Offer $offer)
    {
        $category = null;
        if (!empty($offer->categoryId)) {
            $category = ExternalCategory::find()
                ->andWhere(['outer_id' => $offer->categoryId, 'shop_id' => $this->fileImport->shop_id])
                ->one();
        }

        if (!$category) {
            $category = ExternalCategory::getDefaultCategory($this->fileImport->shop_id);
        }

        return $category;
    }

    /**
     * @param Offer $offer
     * @return ExternalBrand
     */
    protected function getExternalBrand(Offer $offer)
    {
        $brandTitle = trim($offer->vendor);

        if (!empty($brandTitle)) {
            $brand = ExternalBrand::find()
                ->andWhere([
                    'shop_id' => $this->externalProduct->shop_id,
                    'title' => $brandTitle
                ])->one();

            if (!$brand) {
                $brand = new ExternalBrand([
                    'shop_id' => $this->externalProduct->shop_id,
                    'title' => $brandTitle
                ]);
                $brand->setFileImportId($this->fileImport->primaryKey);
                $brand->save();
                $this->logger->log("Create new brand: " . json_encode($brand->toArray()));
            }

            return $brand;
        } else {
            return ExternalBrand::getDefaultBrand($this->fileImport->shop_id);
        }
    }

    /**
     * @param Offer $offer
     * @return ExternalProduct
     */
    protected function getExternalProduct(Offer $offer)
    {
        $outerId = trim($offer->id);
        $this->logger->log("Start parse offer with outer id: $outerId");
        $externalProduct = ExternalProduct::find()
            ->andWhere(['outer_id' => $outerId, 'shop_id' => $this->fileImport->shop_id])
            ->one();

        $this->logger->log("Offer is " . ($externalProduct ? 'exists, id: ' . $externalProduct->primaryKey : 'not exists'));

        if (!$externalProduct) {
            $externalProduct = new ExternalProduct([
                'shop_id' => $this->fileImport->shop_id,
                'outer_id' => $outerId,
                'file_import_id' => $this->fileImport->primaryKey
            ]);
        }

        $externalProduct->is_available = $offer->isAvailable;
        $externalProduct->price = round($offer->price);
        if (!$externalProduct->is_manual) {
            $externalProduct->title = $offer->name;
            $externalProduct->currency_id = $offer->currencyId;
            $externalProduct->picture = $offer->picture;
            $externalProduct->url = $offer->url;
            $externalProduct->type_prefix = !empty($offer->typePrefix) ?
                StringHelper::mbUcFirst($offer->typePrefix) : '';
            $externalProduct->model = $offer->model;
        }

        return $externalProduct;
    }

    /**
     * Попытка смены категории, используется если название категории и бренда совпадают
     */
    protected function changeCategory()
    {
        $categoryParent = $this->categoriesTree->getParent($this->externalProduct->category->outer_id);
        if (!$categoryParent) {
            return;
        }
        $category = ExternalCategory::find()
            ->andWhere([
                'outer_id' => $categoryParent->id,
                'shop_id' => $this->externalProduct->shop_id
            ])->one();
        if ($category) {
            $this->externalProduct->category_id = $category->id;
        }
    }

    /**
     * @param string $productTitle
     * @return string
     */
    protected function getProductModelFromTitle($productTitle)
    {
        if (empty($productTitle)) {
            return $productTitle;
        }

        $this->logger->log('Название товара: ' . $productTitle);

        $productModelParts = array_filter(preg_split('#\s+#', $productTitle));
        $productModelParts = \Yii::$app->phpMorphy->getOrderedBaseFormsList($productModelParts);

        $categoriesTitles = [$this->externalProduct->category->title];
        $categoriesTitles = array_merge($categoriesTitles, $this->externalProduct->category->getVariations());
        $categoriesParents = $this->categoriesTree->getParents($this->externalProduct->category->outer_id);
        foreach ($categoriesParents as $parent) {
            $categoriesTitles[] = $parent->title;
        }

        $hasSubstitute = false;
        if ($this->externalProduct->type_prefix) {
            array_unshift($categoriesTitles, $this->externalProduct->type_prefix);
        } else {
            $this->externalProduct->type_prefix = null;
            $this->externalProduct->save();
            $userCategoriesVariations = $this->getCategoriesVariations($this->externalProduct->category->outer_id);
            foreach ($userCategoriesVariations as $variation) {
                if ($this->substituteByVariation($variation, $productModelParts)) {
                    //$this->externalProduct->type_prefix = $variation;
                    $this->externalProduct->save();
                    $hasSubstitute = true;
                }
            }
        }

        if (!$hasSubstitute) {
            foreach ($categoriesTitles as $categoriesTitle) {
                $this->substituteByVariation($categoriesTitle, $productModelParts);
            }
        }

        $productTitle = implode(' ', ArrayHelper::getColumn($productModelParts, 'original'));;
        $productTitle = $this->cleanSlashes($productTitle);
        $productTitle = $this->removeWordExceptionsFromTitle($productTitle);

        $brandTitles = [$this->externalProduct->brand->title];
        $brandTitles = array_merge($brandTitles, $this->externalProduct->brand->getVariations());
        foreach ($brandTitles as $brandTitle) {
            $productTitle = preg_replace("#(\\s|\\b)$brandTitle(\\s|\\b)#iu", ' ', $productTitle);
        }

        $productTitle = $this->cleanSlashes($productTitle);

        $title = trim($productTitle, "-, ");
        //удаляем слеши, до или после которых имеется пробельный символ
        $title = preg_replace('#((?<=\s)(/)|(/)(?=\s|$))#', '', $title);
       // $title = preg_replace('#(?<!\w)[а-я]{1}$#ui', '', $title);
        $title = trim(preg_replace('#\s+#', ' ', $title), ",-_/");

        $title = $this->cleanSlashes($title);

        return trim(preg_replace('#\s+#', ' ', $title));
    }

    /**
     * @param string $title
     * @return mixed
     */
    protected function cleanSlashes($title)
    {
        $title = preg_replace('#\s+(?=[\/-])#', '', $title);
        $title = preg_replace('#(?<=[\/-])\s+#', '', $title);

        return $title;
    }

    /**
     * Вырезаем части из названия товара
     *
     * @param array $variationForms
     * @param array $productModelParts
     * @return bool
     */
    protected function substitute($variationForms, &$productModelParts)
    {
        $hasSubstitute = false;

        $variationPartsCount = count($variationForms);
        $matchedKeys = [];
        foreach ($productModelParts as $i => $modelPart) {
            $modelPartsForm = $modelPart['base'];
            $hasMatch = false;
            foreach ((array)$modelPartsForm as $modelPartForm) {
                foreach ($variationForms as $variationForm) {
                    if (is_array($variationForm) && in_array($modelPartForm, $variationForm)
                        || $variationForm == $modelPartForm
                    ) {
                        $hasMatch = true;
                        break;
                    }
                }
            }
            if ($hasMatch) {
                $matchedKeys[] = $i;
            } else {
                $matchedKeys = [];
            }

            //есть совпадение категории
            if (count($matchedKeys) == $variationPartsCount) {
                foreach ($matchedKeys as $matchedKey) {
                    unset($productModelParts[$matchedKey]);
                }
                $hasSubstitute = true;
                break;
            }
        }


        return $hasSubstitute;
    }

    /**
     * @param string $variation
     * @param array $productModelParts
     * @return bool
     */
    protected function substituteByVariation($variation, &$productModelParts)
    {
        $variationParts = array_filter(preg_split('#\s+#', $variation));

        $variationForms = (array)\Yii::$app->phpMorphy->getOrderedBaseForms($variationParts);

        return $this->substitute($variationForms, $productModelParts);
    }

    /**
     * Возвращает вариации категорий
     *
     * @param int $outerCategoryId
     * @return array
     */
    protected function getCategoriesVariations($outerCategoryId)
    {
        static $cache = [];

        if (!array_key_exists($outerCategoryId, $cache)) {
            $categoriesIds = [$outerCategoryId];
            $parents = $this->categoriesTree->getParents($outerCategoryId);
            $categoriesIds = array_merge($categoriesIds, ArrayHelper::getColumn($parents, 'id'));
            $categories = $this->getCategoriesByOuterId($categoriesIds);
            $variations = [];
            foreach ($categories as $category) {
                $categoryVariations = $category->getVariations();
                if (!empty($categoryVariations)) {
                    foreach ($categoryVariations as $variation) {
                        $variations[] = $variation;
                    }
                } else {
                    $variations[] = $category->title;
                }
            }
            $cache[$outerCategoryId] = array_values(array_unique($variations));
        }

        return $cache[$outerCategoryId];
    }

    /**
     * @param int[] $outerIds
     * @return ExternalCategory[]
     */
    protected function getCategoriesByOuterId($outerIds)
    {
        $categories = $this->getCategories();

        $result = [];
        foreach ((array)$outerIds as $outerId) {
            if (array_key_exists($outerId, $categories)) {
                $result[] = $categories[$outerId];
            }
        }

        return $result;
    }

    /**
     * @return ExternalCategory[]
     */
    protected function getCategories()
    {
        static $categories;

        if (is_null($categories)) {
            $categories = ExternalCategory::find()
                ->andWhere([
                    'shop_id' => $this->externalProduct->shop_id,
                ])
                ->indexBy('outer_id')
                ->all();
        }

        return $categories;
    }

    /**
     * @param string $productTitle
     * @return array|null
     */
    protected function matchBrandByTitle($productTitle)
    {
        foreach ($this->getBrands() as $brandInfo) {
            if (preg_match("#{$brandInfo['title']}#iu", $productTitle)) {
                return $brandInfo;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getBrands()
    {
        if (is_null(self::$brandList)) {
            self::$brandList = ExternalBrand::find()
                ->select('id, title')
                ->andWhere(['shop_id' => $this->fileImport->shop_id])
                ->asArray()
                ->all();
        }

        return self::$brandList;
    }

    /**
     * @return WordExceptionSaver
     */
    protected function getWordExceptionSaver()
    {
        if (is_null(self::$wordExceptionSaver)) {
            self::$wordExceptionSaver = new WordExceptionSaver($this->fileImport->shop);
            $phrases = WordException::find()
                ->select('word')
                ->andWhere(['shop_id' => $this->fileImport->shop_id])
                ->column();
            self::$wordExceptionSaver->addPhrases($phrases);
        }

        return self::$wordExceptionSaver;
    }

    /**
     * Удаляет слова исключения из заголовка
     *
     * @param string $title
     * @return string
     */
    protected function removeWordExceptionsFromTitle($title)
    {
        $wordExceptions = $this->getWordExceptionSaver()->getSortedPhrases();
        foreach ($wordExceptions as $wordException) {
            $wordException = preg_quote(trim($wordException));
            $title = preg_replace("#((?<=\\s)|/|-|_|\\b|\\()($wordException)((?=\\s)|\\)|/|-|_|\\b|$)#ui", ' ', $title);
        }
        $titleParts = $this->getCachedTitleBaseForms($title);
        foreach ($wordExceptions as $wordException) {
            $wordException = trim($wordException);
            $wordBaseForms = $this->getCachedBaseForms($wordException);
            $this->substitute($wordBaseForms, $titleParts);
        }

        return implode('', array_column($titleParts, 'original'));
    }

    /**
     * @param string $title
     * @return mixed
     */
    protected function getCachedTitleBaseForms($title)
    {
        static $cache = [];
        $title = trim($title);

        if (!isset($cache[$title])) {
            $titleParts = array_filter(preg_split('#([\s/,_]+)#u', $title, -1, PREG_SPLIT_DELIM_CAPTURE));
            $cache[$title] = \Yii::$app->phpMorphy->getOrderedBaseFormsList($titleParts);
        }

        return $cache[$title];
    }

    /**
     * Метод кэширует получение базовых форм для переданных слов
     *
     * @param string $wordException
     * @return mixed
     */
    protected function getCachedBaseForms($wordException)
    {
        static $cache = [];
        $wordException = trim($wordException);

        if (!isset($cache[$wordException])) {
            $words = array_filter(preg_split('#\s+#', $wordException));
            $cache[$wordException] = ArrayHelper::flatten(\Yii::$app->phpMorphy->getOrderedBaseForms($words));
        }

        return $cache[$wordException];
    }
}
