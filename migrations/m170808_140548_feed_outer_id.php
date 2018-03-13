<?php

use yii\db\Migration;

class m170808_140548_feed_outer_id extends Migration
{
    public function up()
    {
        $this->addColumn('feed_item', 'outer_id', $this->string()->comment('Оригинальный id'));
        $this->createIndex('idx_outer_id', 'feed_item', 'outer_id');
    }

    public function down()
    {
        $this->dropColumn('feed_item', 'outer_id');
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
