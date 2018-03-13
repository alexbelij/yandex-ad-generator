<?php

use yii\db\Migration;

class m161211_113852_is_duplicate extends Migration
{
    public function up()
    {
        $this->addColumn('product', 'is_duplicate', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('product', 'is_duplicate');
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
