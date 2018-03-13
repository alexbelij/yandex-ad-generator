<?php

use yii\db\Migration;

class m161029_081213_change_product_id_type extends Migration
{
    public function up()
    {
        $this->alterColumn('product', 'product_id', $this->string());
    }

    public function down()
    {
        return true;
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
