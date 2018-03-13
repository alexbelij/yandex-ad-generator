<?php

use yii\db\Migration;

class m170114_110437_black_list extends Migration
{
    public function up()
    {
        $this->createTable('black_list', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1024),
            'type' => $this->string(),
            'shop_id' => $this->integer()
        ]);

        $this->createIndex('black_list_name_type', 'black_list', ['name(200)', 'type']);
        $this->addForeignKey('black_list_shop_id', 'black_list', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        $this->dropTable('black_list');
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
