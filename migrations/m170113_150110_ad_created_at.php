<?php

use yii\db\Migration;

class m170113_150110_ad_created_at extends Migration
{
    public function up()
    {
        $this->addColumn('ad', 'created_at', $this->dateTime()->after('keywords')->comment('Дата создания объявления'));
        $this->addColumn('ad', 'generated_at', $this->dateTime()->after('updated_at')->comment('Дата генерации объявления'));
    }

    public function down()
    {
        $this->dropColumn('ad', 'created_at');
        $this->dropColumn('ad', 'generated_at');
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
