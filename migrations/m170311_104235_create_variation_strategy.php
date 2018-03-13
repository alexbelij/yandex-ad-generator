<?php

use yii\db\Migration;

class m170311_104235_create_variation_strategy extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'variation_strategy', $this->string()->comment('Стратегия генерации вариаций'));
    }

    public function down()
    {
        $this->dropColumn('shop', 'variation_strategy');
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
