<?php

namespace app\modules\feed\lib\parsers;

/**
 * Interface FeedParserInterface
 * @package app\modules\feed\lib\parsers
 */
interface FeedParserInterface
{
    /**
     * Обработчик, вызываемый при открытии тега
     *
     * @param resource $parser
     * @param string $tagName
     * @param array $attrs
     */
    public function startTag($parser, $tagName, $attrs);

    /**
     * Обработчик, при завершающем теге
     *
     * @param resource $parser
     * @param string $tagName
     */
    public function endTag($parser, $tagName);

    /**
     * Обработчик содержимого тега
     *
     * @param resource $parser
     * @param string $data
     */
    public function characterData($parser, $data);
}
