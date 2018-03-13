<?php

use yii\db\Migration;

class m161007_095652_category_product extends Migration
{
    public function up()
    {
        $this->addColumn('product', 'category_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('product', 'category_id');
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
