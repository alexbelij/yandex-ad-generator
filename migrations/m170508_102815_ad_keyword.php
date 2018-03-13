<?php

use yii\db\Migration;

class m170508_102815_ad_keyword extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk-ad_keyword-ad_id', 'ad_keyword');
        $this->dropIndex('idx-ad_id-keyword', 'ad_keyword');
        $this->alterColumn('ad_keyword', 'keyword', $this->text()->comment('Ключевая фраза')->defaultValue(null));
        $this->createIndex('idx-ad_id-keyword', 'ad_keyword', ['ad_id']);
        $this->addForeignKey(
            'fk-ad_keyword-ad_id',
            'ad_keyword',
            'ad_id',
            'ad',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        $this->alterColumn('ad_keyword', 'keyword', $this->string()->comment('Ключевая фраза'));
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
