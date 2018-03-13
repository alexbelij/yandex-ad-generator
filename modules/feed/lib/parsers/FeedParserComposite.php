<?php

namespace app\modules\feed\lib\parsers;

use app\modules\feed\lib\FeedException;

/**
 * Class FeedParserComposite
 * @package app\modules\feed\lib\parsers
 */
class FeedParserComposite implements FeedParserInterface
{
    /**
     * @var FeedParserInterface[]
     */
    protected $parsers = [];

    /**
     * @param string $name
     * @param FeedParserInterface $parser
     * @return $this
     */
    public function registerParser($name, FeedParserInterface $parser)
    {
        $this->parsers[$name] = $parser;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function startTag($parser, $tagName, $attrs)
    {
        foreach ($this->parsers as $parser) {
            $parser->startTag($parser, $tagName, $attrs);
        }
    }

    /**
     * @inheritDoc
     */
    public function endTag($parser, $tagName)
    {
        foreach ($this->parsers as $parser) {
            $parser->endTag($parser, $tagName);
        }
    }

    /**
     * @inheritDoc
     */
    public function characterData($parser, $data)
    {
        foreach ($this->parsers as $parser) {
            $parser->characterData($parser, $data);
        }
    }

    /**
     * @param string $name
     * @return FeedParserInterface
     * @throws FeedException
     */
    public function getParser($name)
    {
        if (empty($this->parsers[$name])) {
            throw new FeedException('Парсер не зарегистрирован: ' . $name);
        }

        return $this->parsers[$name];
    }
}
