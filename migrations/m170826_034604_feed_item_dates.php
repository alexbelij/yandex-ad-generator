<?php

use yii\db\Migration;

class m170826_034604_feed_item_dates extends Migration
{
    public function up()
    {
        $this->addColumn('feed_item', 'created_at', $this->timestamp()->defaultValue(null)->comment('Дата создания'));
        $this->addColumn('feed_item', 'updated_at', $this->timestamp()->defaultValue(null)->comment('Дата обновления'));
    }

    public function down()
    {
        $this->dropColumn('feed_item', 'created_at');
        $this->dropColumn('feed_item', 'updated_at');
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
