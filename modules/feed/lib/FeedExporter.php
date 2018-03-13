<?php

namespace app\modules\feed\lib;

use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedCategory;
use app\modules\feed\models\FeedQueue;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Экспорт фида по данным из запроса
 *
 * Class FeedExporter
 * @package app\modules\feed\lib
 */
class FeedExporter
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var FeedQueue
     */
    protected $feedQueue;

    /**
     * FeedExporter constructor.
     * @param Feed $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
        $this->feedQueue = FeedQueue::find()
            ->andWhere(['feed_id' => $this->feed->primaryKey])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    /**
     * @param ActiveQuery $query
     * @return string
     */
    public function export(ActiveQuery $query)
    {
        $template = $this->feedQueue->template;

        $feed = strtr($template, [
            '[:categories]' => $this->getCategories(),
            '[:offers]' => $this->getOffers($query)
        ]);

        return $feed;
    }

    /**
     * @return string
     */
    protected function getCategories()
    {
        $categories = "<categories>\n";

        $categoriesList = FeedCategory::find()
            ->andWhere(['feed_id' => $this->feed->id])
            ->asArray()
            ->all();

        foreach ($categoriesList as $categoryData) {
            $attrs = ["id=\"{$categoryData['id']}\""];
            if (!empty($categoryData['parent_id'])) {
                $attrs[] = "parentId=\"{$categoryData['parent_id']}\"";
            }
            $categories .= '<category ' . implode(' ', $attrs) . '>'
                . htmlspecialchars($categoryData['title']) . "</category>\n";
        }

        $categories .= "</categories>\n";

        return $categories;
    }

    /**
     * @param ActiveQuery $query
     * @return string
     */
    protected function getOffers(ActiveQuery $query)
    {
        $offers = '<offers>';

        $query->asArray()->select('item_text');

        foreach ($query->each() as $offer) {
            $offers .= preg_replace('#&(?!amp;)#', '&amp;', $offer['item_text']);
        }

        $offers .= '</offers>';

        return $offers;
    }
}
