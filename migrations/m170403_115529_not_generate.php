<?php

use yii\db\Migration;

class m170403_115529_not_generate extends Migration
{
    public function up()
    {
        $this->addColumn(
            'external_product',
            'is_generate_ad',
            $this->boolean()
                ->defaultValue(true)
                ->comment('Генерировать объявления для товара')
        );
    }

    public function down()
    {
        $this->dropColumn('external_product', 'is_generate_ad');
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
