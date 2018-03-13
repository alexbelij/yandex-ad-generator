<?php

use yii\db\Migration;

class m170604_011311_is_shuffle extends Migration
{
    public function up()
    {
        $this->addColumn(
            'shop',
            'is_shuffle_groups',
            $this->boolean()->comment('Функционал мало показов')->defaultValue(false)
        );
    }

    public function down()
    {
        $this->dropColumn('shop', 'is_shuffle_groups');
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
