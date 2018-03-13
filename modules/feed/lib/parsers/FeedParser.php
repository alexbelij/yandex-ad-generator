<?php

namespace app\modules\feed\lib\parsers;

use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedQueue;

/**
 * Class TagParser
 * @package app\modules\feed\lib\parsers
 */
class FeedParser implements FeedParserInterface
{
    /**
     * @var Feed
     */
    protected $feedQueue;

    /**
     * @var FeedCategories
     */
    protected $categoriesParser;

    /**
     * @var FeedParserInterface[]
     */
    protected $parsers = [];

    /**
     * @var string[]
     */
    protected $tags = [];

    /**
     * TagParser constructor.
     * @param FeedQueue $feedQueue
     */
    public function __construct(FeedQueue $feedQueue)
    {
        $this->feedQueue = $feedQueue;
    }

    /**
     * Обработчик, вызываемый при открытии тега
     *
     * @param resource $res
     * @param string $tagName
     * @param array $attrs
     */
    public function startTag($res, $tagName, $attrs)
    {
        $parser = $this->createParser($tagName);
        if ($parser) {
            $this->tags[] = $tagName;
            $this->parsers[$tagName] = $parser;
        } else {
            $parser = $this->getCurrentParser();
        }

        if ($parser) {
            $parser->startTag($res, $tagName, $attrs);
        }
    }

    /**
     * @return mixed
     */
    protected function currentTag()
    {
        return end($this->tags);
    }

    /**
     * @return FeedParserInterface|null
     */
    protected function getCurrentParser()
    {
        if (count($this->tags) > 0) {
            return $this->parsers[$this->currentTag()];
        }

        return null;
    }

    /**
     * Обработчик, при завершающем теге
     *
     * @param resource $res
     * @param string $tagName
     */
    public function endTag($res, $tagName)
    {
        $parser = $this->getCurrentParser();
        if ($parser) {
            $parser->endTag($res, $tagName);
            if ($tagName == $this->currentTag()) {
                array_pop($this->tags);
            }
        }
    }

    /**
     * Обработчик содержимого тега
     *
     * @param resource $res
     * @param string $data
     */
    public function characterData($res, $data)
    {
        $parser = $this->getCurrentParser();
        if ($parser) {
            $parser->characterData($res, $data);
        }
    }

    /**
     * @param string $tagName
     * @return FeedParserInterface
     */
    protected function createParser($tagName)
    {
        switch ($tagName) {
            case 'categories':
                return $this->getFeedCategoriesParser();
            case 'offer':
                return new FeedOffer($this->feedQueue, $this->getFeedCategoriesParser());
        }

        return null;
    }

    /**
     * @return FeedCategories
     */
    protected function getFeedCategoriesParser()
    {
        static $parser;

        if (is_null($parser)) {
            $parser = new FeedCategories($this->feedQueue);
        }

        return $parser;
    }
}
