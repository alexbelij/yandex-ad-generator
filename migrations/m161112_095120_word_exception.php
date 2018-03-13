<?php

use yii\db\Migration;

class m161112_095120_word_exception extends Migration
{
    public function up()
    {
        $this->createTable('word_exception', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'word' => $this->string(1024)
        ]);
        $this->addForeignKey('fk_word_exception_shop_id', 'word_exception', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        $this->dropTable('wor_exception');
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
