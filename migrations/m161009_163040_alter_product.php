<?php

use yii\db\Migration;

class m161009_163040_alter_product extends Migration
{
    public function up()
    {
        $this->addColumn('external_product', 'original_title', $this->string());
    }

    public function down()
    {
        $this->dropColumn('external_product', 'original_title');
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
