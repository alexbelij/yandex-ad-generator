<?php

use yii\db\Migration;

class m170826_021826_change_feeds extends Migration
{
    public function up()
    {
        $this->addColumn('feed_brand', 'feed_id', $this->integer()->comment('Фид'));
        $this->addColumn('feed_category', 'feed_id', $this->integer()->comment('Фид'));
        $this->addColumn('feed_item', 'feed_id', $this->integer()->comment('Фид'));

        $this->addForeignKey(
            'fk_feed_brand_feed_id',
            'feed_brand',
            'feed_id',
            'feed',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_category_feed_id',
            'feed_category',
            'feed_id',
            'feed',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_item_feed_id',
            'feed_item',
            'feed_id',
            'feed',
            'id',
            'cascade'
        );

        $this->dropForeignKey('fk_feed_category_feed', 'feed_category');
        $this->dropForeignKey('fk_feed_brand_feed', 'feed_brand');
        $this->dropForeignKey('fk_feed_item_feed', 'feed_item');

        $this->dropForeignKey('fk_feed_item_category', 'feed_item');

        $this->dropPrimaryKey('primary key', 'feed_category');
        $this->addPrimaryKey('primary key', 'feed_category', ['id', 'feed_id']);

        $this->addForeignKey(
            'fk_feed_item_category2',
            'feed_item',
            ['feed_id', 'category_id'],
            'feed_category',
            ['feed_id', 'id'],
            'cascade',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_brand_feed_id', 'feed_brand');
        $this->dropForeignKey('fk_feed_category_feed_id', 'feed_category');
        $this->dropForeignKey('fk_feed_item_feed_id', 'feed_item');

        $this->dropColumn('feed_brand', 'feed_id');
        $this->dropColumn('feed_category', 'feed_id');
        $this->dropColumn('feed_item', 'feed_id');

    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
