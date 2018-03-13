<?php

use yii\db\Migration;

class m161009_042604_shop_strategy extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'strategy_factory', $this->string());
    }

    public function down()
    {
        $this->dropColumn('shop', 'strategy_factory');
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
