<?php

use yii\db\Migration;

class m170331_121128_source_title extends Migration
{
    public function up()
    {
        $this->addColumn(
            'external_category',
            'original_title',
            $this->string()->comment('Исходное название категории')->after('title')
        );

        $this->addColumn(
            'external_brand',
            'original_title',
            $this->string()->comment('Исходное название бренда')->after('title')
        );
    }

    public function down()
    {
        $this->dropColumn('external_category', 'original_title');
        $this->dropColumn('external_brand', 'original_title');
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
