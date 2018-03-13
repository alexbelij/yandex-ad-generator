<?php

use yii\db\Migration;

class m170830_134123_feed_name extends Migration
{
    public function up()
    {
        $this->addColumn('feed_item', 'name', $this->string(256));
    }

    public function down()
    {
        $this->dropColumn('feed_item', 'name');
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
