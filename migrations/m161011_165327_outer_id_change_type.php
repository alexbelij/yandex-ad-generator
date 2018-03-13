<?php

use yii\db\Migration;

class m161011_165327_outer_id_change_type extends Migration
{
    public function up()
    {
        $this->alterColumn('external_product', 'outer_id', $this->string());
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
