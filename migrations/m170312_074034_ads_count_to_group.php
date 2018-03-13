<?php

use yii\db\Migration;

class m170312_074034_ads_count_to_group extends Migration
{
    public function up()
    {
        $this->addColumn('ad_yandex_group', 'ads_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('ad_yandex_group', 'yandex_campaign_id', $this->integer());

        $this->dropIndex('idx-keywords_count', 'ad_yandex_group');
        $this->createIndex(
            'idx-ad_yandex_group-yandex_campaing_id-ads_count-keywords_count',
            'ad_yandex_group',
            ['yandex_campaign_id', 'serving_status', 'status', 'ads_count', 'keywords_count']
        );

        $this->addForeignKey(
            'fk-ad_yandex_group-yandex_campaing_id',
            'ad_yandex_group',
            'yandex_campaign_id',
            'yandex_campaign',
            'id',
            'cascade',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropIndex('idx-ad_yandex_group-yandex_campaing_id-ads_count-keywords_count', 'ad_yandex_group');
        $this->createIndex('idx-ad_yandex_group-keywords_count', 'ad_yandex_group', 'keywords_count');

        $this->dropForeignKey('fk-ad_yandex_group-yandex_campaing_id', 'ad_yandex_group');

        $this->dropColumn('ad_yandex_group', 'ads_count');
        $this->dropColumn('ad_yandex_group', 'yandex_campaign_id');
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
