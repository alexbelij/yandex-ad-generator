<?php

use yii\db\Migration;

class m170826_043320_fks extends Migration
{
    public function up()
    {
        $this->addForeignKey(
            'fk_feed_item_feed_queue',
            'feed_item',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade'
        );
        $this->addForeignKey(
            'fk_feed_brand_feed_queue',
            'feed_brand',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade'
        );
        $this->addForeignKey(
            'fk_feed_category_feed_queue',
            'feed_category',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_item_feed_queue', 'feed_item');
        $this->dropForeignKey('fk_feed_brand_feed_queue', 'feed_brand');
        $this->dropForeignKey('fk_feed_category_feed_queue', 'feed_category');
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
