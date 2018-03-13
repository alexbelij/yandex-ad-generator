<?php

use yii\db\Migration;

class m160827_051726_change_price_type extends Migration
{
    public function up()
    {
        $this->alterColumn('product', 'price', 'INT UNSIGNED');
        $this->alterColumn('product', 'manual_price', 'INT UNSIGNED');
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
