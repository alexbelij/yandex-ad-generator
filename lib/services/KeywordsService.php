<?php

namespace app\lib\services;

use app\helpers\ArrayHelper;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\query\ErrorInfo;
use app\lib\api\yandex\direct\query\KeywordsQuery;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\variationStrategies\DefaultStrategy;
use app\models\AdKeyword;
use app\models\AdYandexCampaign;

/**
 * Class KeywordsService
 * @package app\lib\services
 */
class KeywordsService extends YandexService
{
    const MAX_KEYWORD_LENGTH = 32;

    /**
     * @var KeywordsResource
     */
    protected $resource;

    /**
     * @var ErrorInfo[]
     */
    protected $errors = [];

    /**
     * KeywordsService constructor.
     * @param KeywordsResource $resource
     */
    public function __construct(KeywordsResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Публикация набора ключевых слов
     *
     * @param AdYandexCampaign $yandexAd
     * @return \app\lib\api\yandex\direct\query\ChangeResult|null
     */
    public function createKeywordsFor(AdYandexCampaign $yandexAd)
    {
        if (!$yandexAd->adYandexGroup) {
            return ChangeResult::createErrorWithMessage('У объявления отсутствует шаблон');
        }

        /** @var AdKeyword[] $keywords */
        $keywordModels = $yandexAd->adYandexGroup->getKeywordsModels();

        if (!$keywordModels) {
            return ChangeResult::createErrorWithMessage('У объявления отсутствуют ключевые слова');
        }

        $keywords = ArrayHelper::getColumn($keywordModels, 'keyword');
        $keywords = $this->filterKeywords($keywords);
        $keywordsData = [];

        foreach ($keywords as $keyword) {
            $keywordsData[] = [
                'Keyword' => $keyword,
                'UserParam1' => $yandexAd->ad->product->externalProduct->getShortUrl(),
                'AdGroupId' => $yandexAd->adYandexGroup->yandex_adgroup_id
            ];
        }

        $result = $this->resource->add($keywordsData);

        foreach ($result->getResult() as $i => $res) {
            if ($res->getId()) {
                $keywordModel = $keywordModels[$i];
                $keywordModel->yandex_id = $res->getId();
                $keywordModel->save();
            }
        }

        return $result;
    }

    /**
     * Публикация кастомных ключевых фраз
     *
     * @param AdYandexCampaign $yandexAd
     * @param array $keywords
     * @return ChangeResult|null
     */
    public function createKeywordsFromArray(AdYandexCampaign $yandexAd, array $keywords)
    {
        $keywords = $this->filterKeywords($keywords);
        $keywordsData = [];

        foreach ($keywords as $keyword) {
            $keywordsData[] = [
                'Keyword' => $keyword,
                'UserParam1' => $yandexAd->ad->product->externalProduct->getShortUrl(),
                'AdGroupId' => $yandexAd->adYandexGroup->yandex_adgroup_id
            ];
        }

        if (!empty($keywords)) {
            return $this->resource->add($keywordsData);
        }

        return new ChangeResult();
    }

    /**
     * Удаление ключевых слов
     *
     * @param AdYandexCampaign $yandexAd
     * @param int $ignoreMaxLength
     * @param bool $filterMaxLength
     * @return null|ChangeResult
     */
    public function deleteKeywords(AdYandexCampaign $yandexAd, $ignoreMaxLength = 0, $filterMaxLength = false)
    {
        $findResult = $this->resource->find(
            new KeywordsQuery(['adGroupIds' => [$yandexAd->yandex_adgroup_id]]),
            ['id', 'adGroupId', 'keyword']
        );
        $items = $findResult->getItems();

        if (!$yandexAd->adYandexGroup) {
            return ChangeResult::createErrorWithMessage('У объявления отсутствует группа');
        }

        $deleteIds = [];

        if ($ignoreMaxLength > 0) {
            foreach ($items as $i => $item) {
                if (mb_strlen($item['Keyword']) == $ignoreMaxLength) {
                    unset($items[$i]);
                    break;
                }
            }
        } elseif (count($items) > 0) {
            unset($items[0]);
        }

        if ($filterMaxLength) {
            foreach ($items as $i => $item) {
                if (mb_strlen($item['Keyword']) >= self::MAX_KEYWORD_LENGTH) {
                    unset($items[$i]);
                }
            }
        }

        foreach ($items as $item) {
            $deleteIds[] = $item['Id'];
        }

        if (!$deleteIds) {
            return null;
        }

        return $this->resource->delete($deleteIds);
    }

    /**
     * Обновление ключевых слов
     *
     * @param AdYandexCampaign $yandexAd
     * @param bool $allowPluses
     * @param bool $filterMaxLength
     * @return null|ChangeResult
     */
    public function updateKeywords(AdYandexCampaign $yandexAd, $allowPluses = true, $filterMaxLength = false)
    {
        $newKeywords = $this->getKeywordsForCreate($yandexAd, $allowPluses, $filterMaxLength);

        if (empty($newKeywords)) {
            return null;
        }

        $newKeywordsItems = [];

        foreach ($newKeywords as $keyword) {
            $newKeywordsItems[] = [
                'Keyword' => $keyword,
                'UserParam1' => $yandexAd->ad->product->externalProduct->getShortUrl(),
                'AdGroupId' => $yandexAd->adYandexGroup->yandex_adgroup_id,
            ];
        }

        return $this->resource->add($newKeywordsItems);
    }

    /**
     * Обновление группы ключевых слов
     *
     * @param AdYandexCampaign $yandexAd
     * @param string $groupId
     * @param bool $allowPluses
     * @param bool $filterMaxLength
     * @return null|ChangeResult
     */
    public function updateGroup(AdYandexCampaign $yandexAd, $groupId, $allowPluses = true, $filterMaxLength = false)
    {
        $newKeywords = $this->getKeywordsForCreate($yandexAd, $allowPluses, $filterMaxLength);

        if (empty($newKeywords)) {
            return null;
        }

        $newKeywordsItems = [];
        foreach ($newKeywords as $keyword) {
            $newKeywordsItems[] = [
                'Keyword' => $keyword,
                'UserParam1' => $yandexAd->ad->product->externalProduct->getShortUrl(),
                'AdGroupId' => $groupId,
            ];
        }

        return $this->resource->add($newKeywordsItems);
    }

    /**
     * @param array $keywords
     * @param bool $filterPluses
     * @return array
     */
    public function filterKeywords(array $keywords, $filterPluses = false)
    {
        $result = [];
        foreach ($keywords as $keyword) {
            $words = array_filter(explode(' ', $keyword), function ($word) {
                return !empty($word) && mb_strlen($word) <= DefaultStrategy::LIMIT_WORD_LENGTH
                    || mb_substr($word, 0, 1) == '-';
            });

            $phraseWords = [];
            $counter = 0;
            foreach ($words as $word) {
                if (mb_substr($word, 0, 1) == '-') {
                    $phraseWords[] = $word;
                } elseif ($counter < 7) {
                    $counter++;
                    $phraseWords[] = $word;
                }
            }

            $phraseResult = implode(' ', $phraseWords);

            if ($filterPluses) {
                $phraseResult = str_replace('+', ' ' , $phraseResult);
            }

            $result[] = $phraseResult;
        }

        return $result;
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param bool $allowPluses
     * @param bool $filterMaxLength
     * @return null|ChangeResult
     * @throws \app\lib\api\yandex\direct\exceptions\YandexException
     */
    protected function getKeywordsForCreate(AdYandexCampaign $yandexAd, $allowPluses = true, $filterMaxLength = false)
    {
        $items = $this->resource->find(
            new KeywordsQuery(['adGroupIds' => [$yandexAd->yandex_adgroup_id]]),
            ['id', 'adGroupId', 'keyword']
        );

        if ($filterMaxLength) {
            foreach ($items as $i => $item) {
                if (mb_strlen($item['Keyword']) >= self::MAX_KEYWORD_LENGTH) {
                    unset($items[$i]);
                }
            }
        }

        if (!$yandexAd->adYandexGroup) {
            return ChangeResult::createErrorWithMessage('У объявления отсутствует группа');
        }

        $keywords = $yandexAd->adYandexGroup->getKeywords();
        $keywords = $this->filterKeywords($keywords, !$allowPluses);
        $deleteIds = [];
        $existsKeywords = [];
        foreach ($items as $item) {
            if (!in_array(trim($item['Keyword']), $keywords)) {
                $deleteIds[] = $item['Id'];
            } else {
                $existsKeywords[] = $item['Keyword'];
            }
        }

        //ключевые слова для добавления
        $newKeywords = array_diff($keywords, $existsKeywords);

        if (!empty($deleteIds)) {
            $result = $this->resource->delete($deleteIds);
            if (!$result->isSuccess()) {
                $this->errors[] = $result->getErrors();
            }

            if (!$result->isSuccess() && empty($newKeywords)) {
                return null;
            }
        }

        return $newKeywords;
    }
}
