<?php

use yii\db\Migration;

class m170715_112512_feed_sids extends Migration
{
    public function up()
    {
        $this->addColumn('feed', 'subid', $this->string(512));
    }

    public function down()
    {
        $this->dropColumn('feed', 'subid');
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
