<?php

use yii\db\Migration;

class m170914_131702_alter_name extends Migration
{
    public function up()
    {
        $this->alterColumn('feed_item', 'name', $this->string(512));
    }

    public function down()
    {

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
