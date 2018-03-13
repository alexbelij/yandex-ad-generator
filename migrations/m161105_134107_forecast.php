<?php

use yii\db\Migration;

class m161105_134107_forecast extends Migration
{
    public function up()
    {
        $this->createTable('forecast', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'brand_id' => $this->integer(),
            'points' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey('fk_forecast_shop_id', 'forecast', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        $this->dropForeignKey('fk_forecast_shop_id', 'forecast');
        $this->dropTable('forecast');
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
