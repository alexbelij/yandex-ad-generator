<?php

namespace app\modules\feed\lib\parsers;

/**
 * Class FeedTemplate
 * @package app\modules\feed\lib\parsers
 */
class FeedTemplateParser implements FeedParserInterface
{
    /**
     * @var string
     */
    protected $template = '';

    /**
     * Какие теги заменить на плейсхолдеры
     *
     * @var array
     */
    protected $templateTags = [
        'categories',
        'offers'
    ];

    /**
     * @var bool
     */
    protected $needLogging = true;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->template = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">";
    }

    /**
     * Обработчик, вызываемый при открытии тега
     *
     * @param resource $parser
     * @param string $tagName
     * @param array $attrs
     */
    public function startTag($parser, $tagName, $attrs)
    {
        $attrToString = [];
        foreach ($attrs as $key => $value) {
            $attrToString[] = "$key=\"$value\"";
        }
        $tagName = strtolower($tagName);
        if (in_array($tagName, $this->templateTags) || !$this->needLogging) {
            $this->needLogging = false;
        } else {
            if ($attrToString) {
                $this->template .= '<' . $tagName . ' ' . implode(' ', $attrToString) . '>';
            } else {
                $this->template .= "<$tagName>";
            }
        }
    }

    /**
     * Обработчик, при завершающем теге
     *
     * @param resource $parser
     * @param string $tagName
     */
    public function endTag($parser, $tagName)
    {
        if (in_array($tagName, $this->templateTags)) {
            $this->needLogging = true;
            $this->template .= "[:$tagName]";
        } elseif ($this->needLogging) {
            $this->template .= "</$tagName>";
        }
    }

    /**
     * Обработчик содержимого тега
     *
     * @param resource $parser
     * @param string $data
     */
    public function characterData($parser, $data)
    {
        if ($this->needLogging) {
            $this->template .= $data;
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
