<?php

use yii\db\Migration;

class m161017_095531_account_unit extends Migration
{
    public function up()
    {
        $this->addColumn('account', 'units', $this->string());
        $this->dropColumn('shop', 'units');
    }

    public function down()
    {
        $this->dropColumn('account', 'units');
        $this->addColumn('shop', 'units', $this->string());
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
