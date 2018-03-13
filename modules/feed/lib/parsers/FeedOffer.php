<?php

namespace app\modules\feed\lib\parsers;

use app\helpers\ArrayHelper;
use app\helpers\StringHelper;
use app\modules\feed\lib\FeedException;
use app\modules\feed\models\FeedBrand;
use app\modules\feed\models\FeedItem;
use app\modules\feed\models\FeedQueue;
use app\modules\feed\models\FeedRedirect;

/**
 * Class FeedOffer
 * @package app\modules\feed\lib
 */
class FeedOffer implements FeedParserInterface
{
    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var FeedQueue
     */
    protected $feedQueue;

    /**
     * @var FeedCategories
     */
    protected $categories;

    /**
     * @var int
     */
    protected $price;

    /**
     * @var string
     */
    protected $brand;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var array
     */
    protected $category;

    /**
     * @var int
     */
    protected $categoryId;

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var mixed
     */
    protected $offerId = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * FeedOffer constructor.
     * @param FeedQueue $feedQueue
     * @param FeedCategories $feedCategories
     */
    public function __construct(FeedQueue $feedQueue, FeedCategories $feedCategories)
    {
        $this->feedQueue = $feedQueue;
        $this->categories = $feedCategories;
    }

    /**
     * @inheritDoc
     */
    public function startTag($parser, $tagName, $attrs)
    {
        if (!empty($attrs)) {
            $attrToString = [];
            foreach ($attrs as $key => $value) {
                $attrToString[] = "$key=\"$value\"";
            }
            $this->text .= '<' . $tagName . ' ' . implode(' ', $attrToString) . '>';
        } else {
            $this->text .= "<$tagName>";
        }

        if ($tagName == 'offer') {
            $this->offerId = $attrs['id'];
        }
    }

    /**
     * @inheritdoc
     */
    public function characterData($parser, $data)
    {
        $this->text .= htmlspecialchars($data);
    }

    /**
     * Парсинг данных из текста информации о товаре
     * @param string $text
     */
    protected function parseOfferData($text)
    {
        $text = trim(str_replace(["\r\n", "\n"], ' ', $text));

        $matches = [];

        if (preg_match('#<price>(\w+)</price>#', $text, $matches)) {
            $this->price = (int)trim($matches[1]);
        }

        if (preg_match('#<model>(.+?)</model>#', $text, $matches)) {
            $this->model = trim($matches[1]);
        }

        if (preg_match('#<categoryId>(\d+)</categoryId>#', $text, $matches)) {
            $this->categoryId = intval($matches[1]);
            $this->category = $this->categories->getCategoryById($this->categoryId);
        }

        if (preg_match('#<vendor>(.+?)</vendor>#', $text, $matches)) {
            $this->brand = trim($matches[1]);
        }

        if (preg_match('#<name>(.+?)</name>#', $text, $matches)) {
            $this->name = trim($matches[1]);
        }
    }

    /**
     * Возвращает ссылку с подстановкой параметров
     *
     * @param string $url
     * @return string
     */
    protected function getTargetUrl($url)
    {
        $feed = $this->feedQueue->feed;
        if (empty($feed->subid)) {
            return $url;
        }

        $placeholders = [
            '[brand]' => $this->brand,
            '[price]' => $this->price,
            '[model]' => $this->model,
            '[category]' => $this->category['title'],
            '[name]' => $this->name
        ];

        if (count(array_filter($placeholders)) === 0) {
            return $url;
        }

        $subIdParam = strtr($feed->subid, $placeholders);
        $znak = strpos($url, '?') ? '&' : '?';

        return $url . $znak . $subIdParam;
    }

    /**
     * @inheritDoc
     */
    public function endTag($parser, $tagName)
    {
        $this->text .= "</$tagName>";

        if ($tagName == 'offer') {
            $this->parseOfferData($this->text);
            $matches = [];
            $str = $this->text;
            if (preg_match('#<url>(.*)</url>#s', $str, $matches)) {

                $url = trim($matches[1]);
                $hash = md5($url);
                $feedRedirect = FeedRedirect::findOne(['hash_url' => $hash]);

                if (!$feedRedirect) {
                    $feedRedirect = new FeedRedirect([
                        'hash_url' => $hash,
                        'feed_id' => $this->feedQueue->feed->id
                    ]);
                }

                $targetUrl = $this->getTargetUrl($url);
                if ($feedRedirect->target_url != $targetUrl) {
                    $feedRedirect->target_url = $targetUrl;
                    if (!$feedRedirect->save()) {
                        throw new FeedException('Ошибка при сохранении фида');
                    }
                }

                $domain = rtrim($this->feedQueue->feed->domain, '/');
                $str = preg_replace('#<url>(.*)</url>#s', "<url>$domain/f/$hash</url>", $str);
            }

            $feedItem = FeedItem::findOne([
                'outer_id' => $this->offerId,
                'feed_id' => $this->feedQueue->feed_id
            ]);

            if (!$feedItem) {
                $feedItem = new FeedItem([
                    'outer_id' => $this->offerId,
                ]);
            }

            $feedItem->setAttributes([
                'price' => $this->price,
                'category_id' => $this->category['id'],
                'item_text' => $str,
                'feed_id' => $this->feedQueue->feed_id,
                'feed_queue_id' => $this->feedQueue->primaryKey,
                'brand_id' => ArrayHelper::getValue($this->getBrand(), 'id'),
                'name' => StringHelper::truncateByWordsDirect($this->name, 512)
            ]);

            if (!$feedItem->save()) {
                echo 'Ошибка при сохранении фида' . PHP_EOL;
            }
        }
    }

    /**
     * @return FeedBrand|null
     */
    protected function getBrand()
    {
        if ($this->brand) {
            $feedBrand = FeedBrand::findOne([
                'feed_id' => $this->feedQueue->feed_id,
                'title' => trim($this->brand)
            ]);
            if (!$feedBrand) {
                $feedBrand = new FeedBrand([
                    'feed_id' => $this->feedQueue->feed_id,
                    'feed_queue_id' => $this->feedQueue->primaryKey,
                    'title' => trim($this->brand)
                ]);
                $feedBrand->save();
            }

            return $feedBrand;
        }

        return null;
    }
}
