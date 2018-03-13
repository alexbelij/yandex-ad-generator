<?php

use yii\db\Migration;

class m161105_020445_category_variations extends Migration
{
    public function up()
    {
        $this->addColumn('external_category', 'variations', $this->string(1024));
    }

    public function down()
    {
        $this->dropColumn('external_category', 'variations');
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
