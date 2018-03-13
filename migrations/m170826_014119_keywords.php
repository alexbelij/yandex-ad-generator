<?php

use yii\db\Migration;

class m170826_014119_keywords extends Migration
{
    public function up()
    {
        $this->addColumn('ad_keyword', 'created_at', $this->timestamp()->comment('Дата создания'));
        $this->addColumn('ad_keyword', 'updated_at', $this->timestamp()->defaultValue(null)->comment('Дата обновления'));
    }

    public function down()
    {
        $this->dropColumn('ad_keyword', 'created_at');
        $this->dropColumn('ad_keyword', 'updated_at');
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
