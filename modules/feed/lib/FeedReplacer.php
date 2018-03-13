<?php

namespace app\modules\feed\lib;

use app\modules\feed\lib\parsers\FeedParser;
use app\modules\feed\lib\parsers\FeedParserComposite;
use app\modules\feed\lib\parsers\FeedTemplateParser;
use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedItem;
use app\modules\feed\models\FeedQueue;

/**
 * Класс заменяет урлы в фиде
 *
 * Class FeedReplacer
 * @package app\modules\feed\lib
 */
class FeedReplacer
{
    /**
     * @var FeedQueue
     */
    public $feedQueue;

    /**
     * FeedReplacer constructor.
     * @param FeedQueue $feedQueue
     */
    public function __construct(FeedQueue $feedQueue)
    {
        $this->feedQueue = $feedQueue;
    }

    /**
     * @param string $sourceFile
     * @param string $targetFile
     * @throws FeedException
     */
    public function replace($sourceFile, $targetFile)
    {
        if (false == ($fhSource = fopen($sourceFile, 'r'))) {
            throw new FeedException("Невозможно открыть файл: $sourceFile");
        }

        $content = fread($fhSource, 8192);

        if (strpos($content, 'yml_catalog') === false) {
            throw new FeedException('Неверный формат файла, отсутствует тэг yml_catalog');
        }

        fseek($fhSource, 0);

        $targetDir = dirname($targetFile);

        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 777, true)) {
                throw new FeedException("Ошибка при создании директории: $targetDir");
            }
        }

        $tagParser = new FeedParserComposite();
        $tagParser
            ->registerParser('saveInDb', new FeedParser($this->feedQueue))
            ->registerParser('template', new FeedTemplateParser());

        $fhTarget = fopen($targetFile, 'w');

        $saxParser = xml_parser_create('utf-8');
        xml_parser_set_option($saxParser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($saxParser, [$tagParser, 'startTag'], [$tagParser, 'endTag']);
        xml_set_character_data_handler($saxParser, [$tagParser, 'characterData']);

        while ($data = fread($fhSource, 8192)) {
            xml_parse($saxParser, $data);
        }

        xml_parser_free($saxParser);

        $this->feedQueue->template = $tagParser->getParser('template')->getTemplate();
        $this->feedQueue->save();

        if (!feof($fhSource)) {
            throw new FeedException('Ошибка при чтении файла - ' . $sourceFile);
        }

        fclose($fhSource);
        fclose($fhTarget);
        chmod($targetFile, 766);

        FeedItem::deleteAll([
            'AND',
            ['feed_id' => $this->feedQueue->feed_id],
            ['<', 'updated_at', $this->feedQueue->created_at]
        ]);
    }
}
