<?php

use yii\db\Migration;

class m170711_131302_shuffle_strategy extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'shuffle_strategy', $this->string()->comment('Стратегия мало показов'));
    }

    public function down()
    {
        $this->dropColumn('shop', 'shuffle_strategy');
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
