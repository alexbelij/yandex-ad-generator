<?php

use yii\db\Migration;

class m170121_030317_alter_templates extends Migration
{
    public function up()
    {
        $this->addColumn('ad_template', 'price_from', $this->bigInteger());
        $this->addColumn('ad_template', 'price_to', $this->bigInteger());

        $this->createTable('ad_template_brand', [
            'ad_template_id' => $this->integer(),
            'brand_id' => $this->integer()
        ]);

        $this->createTable('ad_template_category', [
            'ad_template_id' => $this->integer(),
            'category_id' => $this->integer()
        ]);

        $this->addPrimaryKey('pk_ad_template_brand', 'ad_template_brand', ['ad_template_id', 'brand_id']);
        $this->addPrimaryKey('pk_ad_template_category', 'ad_template_category', ['ad_template_id', 'category_id']);

        $this->addForeignKey(
            'fk_ad_template_brand_ad_template',
            'ad_template_brand',
            'ad_template_id',
            'ad_template',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_ad_template_category_ad_template',
            'ad_template_category',
            'ad_template_id',
            'ad_template',
            'id',
            'cascade',
            'cascade'
        );

//        $this->addForeignKey(
//            'fk_ad_template_category_external_category',
//            'ad_template_category',
//            'category_id',
//            'external_category',
//            'id',
//            'cascade',
//            'cascade'
//        );
    }

    public function down()
    {
        $this->dropColumn('ad_template', 'price_from');
        $this->dropColumn('ad_template', 'price_to');

        $this->dropTable('ad_template_brand');
        $this->dropTable('ad_template_category');
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
