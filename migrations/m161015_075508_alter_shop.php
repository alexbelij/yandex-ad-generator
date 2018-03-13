<?php

use yii\db\Migration;

class m161015_075508_alter_shop extends Migration
{
    public function up()
    {
        $this->addColumn('{{%shop}}', 'account_id', $this->integer());
        $this->addForeignKey('fk_shop_id_account_id', 'shop', 'account_id', 'account', 'id');

        $this->createTable('{{%brand_account}}', [
            'id' => $this->primaryKey(),
            'brand_id' => $this->integer(),
            'account_id' => $this->integer(),
            'shop_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_brand_account_account_id_account', '{{%brand_account}}', 'account_id',
            '{{%account}}', 'id');
        $this->addForeignKey('fk_brand_account_shop_id_shop', '{{%brand_account}}', 'shop_id', '{{%shop}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_shop_id_account_id', '{{%shop}}');
        $this->dropColumn('{{%shop}}', 'account_id');

        $this->dropForeignKey('fk_brand_account_account_id_account', '{{%brand_account}}');
        $this->dropForeignKey('fk_brand_account_shop_id_shop', '{{%brand_account}}');
        $this->dropTable('{{%brand_account}}');
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
