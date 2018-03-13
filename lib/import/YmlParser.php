<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 24.08.16
 * Time: 8:49
 */

namespace app\lib\import;
use app\components\FileLogger;
use app\components\LoggerInterface;
use app\components\LoggerStub;
use app\lib\import\yml\AbstractTagParser;
use app\lib\import\yml\strategies\defaultStrategy\ShopTagParser;
use app\lib\import\yml\strategies\defaultStrategy\YmlCatalogTagParser;
use app\lib\import\yml\strategies\factory\TagParserFactoryInterface;
use app\lib\import\yml\StubTag;
use app\lib\import\yml\TagParserFactory;
use app\models\FileImport;
use app\models\Shop;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class YmlParser
 * @package app\lib\import
 */
class YmlParser implements ImportInterface
{
    /**
     * @var string
     */
    private $lastTag;

    /**
     * @var FileImport
     */
    private $fileImport;

    /**
     * @var bool
     */
    private $hasEnd;

    /**
     * @var AbstractTagParser[]
     */
    private $parsers = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var FileLogger
     */
    private $logger;

    /**
     * @var TagParserFactoryInterface
     */
    private $tagParserFactory;

    /**
     * YmlParser constructor.
     * @param TagParserFactoryInterface $strategyFactory
     * @param FileLogger|null $logger
     */
    public function __construct(TagParserFactoryInterface $strategyFactory, FileLogger $logger = null)
    {
        if (is_null($logger)) {
            $logger = new LoggerStub();
        }
        $this->tagParserFactory = $strategyFactory;
        $this->logger = $logger;
    }

    /**
     * @param FileLogger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return FileLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param resource $parser
     * @param string $tagName
     * @param array $attrs
     */
    public function startElements($parser, $tagName, $attrs)
    {
        $tagName = strtolower($tagName);
        $this->lastTag = $tagName;
        $this->hasEnd = false;
        $tagParser = $this->addTagParser($tagName);
        if (!$tagParser) {
            return;
        }

        foreach ($attrs as $key => $value) {
            $attrs[mb_strtoupper($key)] = $value;
        }

        $tagParser->parseAttributes($tagName, $attrs);
    }

    /**
     * @param resource $parser
     * @param string $tagName
     */
    public function endElements($parser, $tagName)
    {
        $tagName = strtolower($tagName);
        $tagParser = $this->getCurrentTagParser();
        if ($tagParser) {
            $tagParser->end($tagName);
        }
        $this->hasEnd = true;

        if ($this->getProcessedTag() == $tagName) {
            array_pop($this->tags);
            unset($this->parsers[$tagName]);
        }
    }

    /**
     * @param resource $parser
     * @param string $data
     */
    public function characterData($parser, $data)
    {
        $tagParser = $this->getCurrentTagParser();
        if ($tagParser && !$this->hasEnd) {
            $tagParser->parseCharacters($this->lastTag, $data);
        }
    }

    /**
     * @param string $tagName
     * @return AbstractTagParser
     */
    private function addTagParser($tagName)
    {
        $tagParser = null;
        switch ($tagName) {
            case 'yml_catalog':
                $tagParser = $this->tagParserFactory->createYmlCatalogTagParser();
                break;
            case 'shop':
                $tagParser = $this->tagParserFactory->createShopTagParser();
                break;
            case 'categories':
                $tagParser = $this->tagParserFactory->createCategoriesTagParser();
                break;
            case 'offers':
                $tagParser = $this->tagParserFactory->createOffersTagParser();
                break;
            case 'category':
                $tagParser = $this->tagParserFactory->createCategoryTagParser();
                break;
            case 'offer':
                $tagParser = $this->tagParserFactory->createOfferTagParser();
                break;
        }

        if ($tagParser) {
            $this->tags[] = $tagName;
            $this->parsers[$tagName] = $tagParser;
        }

        return $tagParser;
    }

    /**
     * @param null|string $tagName
     * @return mixed
     */
    protected function getCurrentTagParser($tagName = null)
    {
        $tagName = $tagName ?: $this->getProcessedTag();

        if (!$tagName) {
            return null;
        }

        return ArrayHelper::getValue($this->parsers, $tagName);
    }

    /**
     * @return mixed
     */
    protected function getProcessedTag()
    {
        return end($this->tags);
    }

    /**
     * @param FileImport $fileImport
     * @return bool
     * @throws ImportException
     */
    public function import(FileImport $fileImport)
    {
        $this->fileImport = $fileImport;
        $fileName = $fileImport->filename;

        if (false == ($fh = fopen($fileName, 'r'))) {
            throw new ImportException("Невозможно открыть файл: $fileName");
        }

        $content = fread($fh, 8192);

        if (strpos($content, 'yml_catalog') === false) {
            throw new ImportException('Неверный формат файла');
        }

        fseek($fh, 0);

        $saxParser = xml_parser_create('utf-8');
        xml_parser_set_option($saxParser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($saxParser, [$this, 'startElements'], [$this, 'endElements']);
        xml_set_character_data_handler($saxParser, [$this, 'characterData']);

        while ($data = fread($fh, 8192)) {
            xml_parse($saxParser, $data);
        }

        xml_parser_free($saxParser);

        return true;
    }
}
