<?php

use yii\db\Migration;

class m161024_134335_alter_updated_at extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE ad 
        CHANGE updated_at 
         updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->execute('ALTER TABLE ad ALTER COLUMN updated_at drop DEFAULT ');


        $this->execute("ALTER TABLE product 
        CHANGE updated_at 
         updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->execute('ALTER TABLE product ALTER COLUMN updated_at drop DEFAULT ');

        $this->execute("ALTER TABLE product 
        CHANGE created_at 
         created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->execute('ALTER TABLE product ALTER COLUMN created_at drop DEFAULT ');
    }

    public function down()
    {
        return true;
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
