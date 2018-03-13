<?php

use yii\db\Migration;

class m161030_031427_typePrefix extends Migration
{
    public function up()
    {
        $this->addColumn('external_product', 'type_prefix', $this->string());
        $this->addColumn('shop', 'href_template', $this->string());
    }

    public function down()
    {
        $this->dropColumn('external_product', 'type_prefix');
        $this->dropColumn('shop', 'href_template');
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
