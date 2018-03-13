<?php

use yii\db\Migration;

class m170730_014701_quick_redirect extends Migration
{
    public function up()
    {
        $this->createTable('quick_redirect', [
            'id' => $this->primaryKey()->unsigned(),
            'source' => $this->string(2048)->comment('Урл, с которого редиректить'),
            'target' => $this->string(2048)->comment('Урл, на который редиректить')
        ]);
    }

    public function down()
    {
        echo "m170730_014701_quick_redirect cannot be reverted.\n";

        return false;
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
