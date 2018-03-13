<?php

namespace app\modules\feed\lib\parsers;

use app\helpers\ArrayHelper;
use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedCategory;
use app\modules\feed\models\FeedQueue;

/**
 * Class FeedCategories
 * @package app\modules\feed\lib
 */
class FeedCategories implements FeedParserInterface
{
    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var Feed
     */
    protected $feedQueue;

    /**
     * @var array
     */
    protected $category = [];

    /**
     * @var string
     */
    protected $title = '';

    /**
     * FeedCategories constructor.
     * @param FeedQueue $feedQueue
     */
    public function __construct(FeedQueue $feedQueue)
    {
        $this->feedQueue = $feedQueue;
    }

    /**
     * @inheritDoc
     */
    public function startTag($parser, $tagName, $attrs)
    {
        $this->category = [];
        $this->title = '';
        if (strtolower($tagName) == 'category') {
            $this->category = [
                'id' => $attrs['id'],
                'parentId' => ArrayHelper::getValue($attrs, 'parentId')
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function endTag($parser, $tagName)
    {
        if ($tagName == 'category') {
            $catId = $this->category['id'];
            $this->categories[$catId] = array_merge(
                $this->category,
                ['title' => $this->title]
            );
        }

        if ($tagName == 'categories') {
            foreach ($this->categories as $category) {
                $feedCategory = FeedCategory::findOne([
                    'feed_id' => $this->feedQueue->feed_id,
                    'id' => $category['id']
                ]);

                if (!$feedCategory) {
                    $feedCategory = new FeedCategory([
                        'id' => $category['id'],
                        'feed_queue_id' => $this->feedQueue->primaryKey,
                        'feed_id' => $this->feedQueue->feed_id,
                        'title' => trim($category['title']),
                        'parent_id' => $category['parentId']
                    ]);
                    $feedCategory->save();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function characterData($parser, $data)
    {
        $this->title .= $data;
    }

    /**
     * @param int $id
     * @param null $default
     * @return mixed
     */
    public function getCategoryById($id, $default = null)
    {
        return ArrayHelper::getValue($this->categories, $id, $default);
    }
}
