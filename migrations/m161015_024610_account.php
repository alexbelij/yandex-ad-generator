<?php

use yii\db\Migration;

class m161015_024610_account extends Migration
{
    public function up()
    {
        $this->createTable('account', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'token' => $this->string(),
            'account_data' => $this->text(),
            'account_type' => $this->string()
        ]);
    }

    public function down()
    {
        $this->dropTable('account');
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
