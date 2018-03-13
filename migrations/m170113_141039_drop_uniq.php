<?php

use yii\db\Migration;

class m170113_141039_drop_uniq extends Migration
{
    public function up()
    {
        $this->dropIndex('external_category_outer_id_shop_id_idx', 'external_category');
        $this->createIndex(
            'external_category_outer_id_shop_id_idx',
            'external_category',
            ['outer_id', 'shop_id']
        );
    }

    public function down()
    {
        $this->createIndex(
            'external_category_outer_id_shop_id_idx',
            'external_category',
            ['outer_id', 'shop_id'],
            true
        );
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
