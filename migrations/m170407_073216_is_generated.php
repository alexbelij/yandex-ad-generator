<?php

use yii\db\Migration;

class m170407_073216_is_generated extends Migration
{
    public function up()
    {
        $this->addColumn('ad_keyword', 'is_generated', $this->boolean()->defaultValue(false)
            ->comment('Объявление было сгенерировано')
        );
    }

    public function down()
    {
        $this->dropColumn('ad_keyword', 'is_generated');
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
