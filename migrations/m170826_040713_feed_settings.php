<?php

use yii\db\Migration;

class m170826_040713_feed_settings extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_feed_search_feed_queue', 'feed_settings');
        $this->dropColumn('feed_settings', 'feed_queue_id');

        $this->addColumn('feed_settings', 'feed_id', $this->integer());
        $this->addForeignKey(
            'fk_feed_settings_feed',
            'feed_settings',
            'feed_id',
            'feed',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        echo "m170826_040713_feed_settings cannot be reverted.\n";

        return false;
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
