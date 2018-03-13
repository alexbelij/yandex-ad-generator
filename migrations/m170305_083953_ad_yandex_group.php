<?php

use yii\db\Migration;

class m170305_083953_ad_yandex_group extends Migration
{
    public function up()
    {
        // Keywords
        $this->createTable('ad_keyword', [
            'id' => $this->primaryKey(),
            'keyword' => $this->string(),
            'ad_id' => $this->integer(),
        ]);
        $this->createIndex('idx-ad_id-keyword', 'ad_keyword', ['ad_id', 'keyword'], true);
        $this->addForeignKey('fk-ad_keyword-ad_id', 'ad_keyword', 'ad_id', 'ad', 'id', 'cascade', 'cascade');

        // Groups
        $this->createTable('ad_yandex_group', [
            'id' => $this->primaryKey(),
            'yandex_adgroup_id' => $this->string(),
            'keywords_count' => $this->integer(),
        ]);
        $this->createIndex('idx-keywords_count', 'ad_yandex_group', 'keywords_count');

        // Campaign
        $this->addColumn('ad_yandex_campaign', 'ad_yandex_group_id', $this->integer());
        $this->addForeignKey('fk-ad_yandex_campaign-ad_yandex_group_id', 'ad_yandex_campaign', 'ad_yandex_group_id', 'ad_yandex_group', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        // Keywords
        $this->dropForeignKey('fk-ad_keyword-ad_id', 'ad_keyword');
        $this->dropTable('ad_keyword');

        // Groups
        $this->dropTable('ad_yandex_group');

        // Campaign
        $this->dropForeignKey('fk-ad_yandex_campaign-ad_yandex_group_id', 'ad_yandex_campaign');
        $this->dropColumn('ad_yandex_campaign', 'ad_yandex_group_id');
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
