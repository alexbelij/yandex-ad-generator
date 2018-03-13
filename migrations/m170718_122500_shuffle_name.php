<?php

use yii\db\Migration;

class m170718_122500_shuffle_name extends Migration
{
    public function up()
    {
        $this->addColumn('variation', 'shuffle_name', $this->string()->comment('Название бренда для мало показов'));
    }

    public function down()
    {
        $this->dropColumn('variation', 'shuffle_name');
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
