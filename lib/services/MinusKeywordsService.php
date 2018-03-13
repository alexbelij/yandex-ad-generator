<?php

namespace app\lib\services;

use app\components\LoggerInterface;
use app\models\AdKeyword;
use yii\db\ActiveQuery;

/**
 * Сервис минусации ключевых фраз
 *
 * Class MinusKeywordsService
 * @package app\lib\services
 */
class MinusKeywordsService
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * MinusKeywordsService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Выполнение минусации ключевых фраз
     *
     * @param array $keywords
     * @return array
     */
    public function execute(array $keywords)
    {
        $result = [];
        foreach ($keywords as $id => $outerKeyword) {
            $keywordParts = $this->splitToParts($outerKeyword);
            $keywordPartsCount = count($keywordParts);
            $hasMinus = false;
            foreach ($keywords as $innerKeyword) {
                if ($innerKeyword == $outerKeyword) {
                    continue;
                }
                $innerKeywordParts = $this->splitToParts($innerKeyword);
                if (count(array_intersect($innerKeywordParts, $keywordParts)) >= $keywordPartsCount) {
                    foreach (array_diff($innerKeywordParts, $keywordParts) as $part) {
                        $hasMinus = true;
                        $minusWord = '-' . $this->prepareWord($part);
                        if (!in_array($minusWord, $keywordParts)) {
                            $keywordParts[] = '-' . $this->prepareWord($part);
                        }
                    }
                }
            }
            if ($hasMinus) {
                $resultKeyword = implode(' ', $keywordParts);
                $this->logger->log('Source: ' . $outerKeyword);
                $this->logger->log($resultKeyword);
                $result[$id] = $resultKeyword;
            }
        }

        return $result;
    }

    /**
     * Разбивает ключевую фразу на части и удаляет лова начинающиеся с "-"
     *
     * @param string $keyword
     * @return array
     */
    protected function splitToParts($keyword)
    {
        $keywordParts = preg_split('#\s+#u', $keyword);
        return array_filter($keywordParts, function ($part) {
            return mb_substr($part, 0, 1) != '-';
        });
    }

    /**
     * @param string $word
     * @return mixed
     */
    protected function prepareWord($word)
    {
        return preg_replace('#[^a-zа-я0-9.]#iu', '', $word);
    }
}
