<?php

use yii\db\Migration;

class m160718_151537_migrate_structure extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%template}}', 'campaign_template_id', $this->integer());
        $this->addColumn('{{%yandex_campaign}}', 'campaign_template_id', $this->integer());
        $this->addColumn('{{%ad}}', 'is_deleted', $this->boolean()->defaultValue(false));

        $this->addForeignKey(
            'fk_template_campaign_template', '{{%template}}', 'campaign_template_id',
            '{{%campaign_template}}', 'id', 'cascade'
        );

        $this->addForeignKey(
            'fk_yandex_campaign_template', '{{%yandex_campaign}}', 'campaign_template_id',
            '{{%campaign_template}}', 'id', 'cascade'
        );

        $this->createTable('ad_yandex_campaign', [
            'id' => $this->primaryKey(),
            'ad_id' => $this->integer(),
            'yandex_campaign_id' => $this->integer(),
            'template_id' => $this->integer(),
            'yandex_ad_id' => $this->bigInteger(),
            'yandex_adgroup_id' => $this->bigInteger(),
            'uploaded_at' => $this->timestamp()
        ], 'ENGINE = INNODB');

        $this->execute("ALTER TABLE {{%ad}} ENGINE = InnoDB");
        $this->execute("ALTER TABLE {{%variation}} ENGINE = InnoDB");
        $this->execute("ALTER TABLE {{%vcard}} ENGINE = InnoDB");

        $this->addForeignKey(
            'fk_ad_yandex_campaign_ad', '{{%ad_yandex_campaign}}', 'ad_id', '{{%ad}}', 'id', 'cascade'
        );

        $this->addForeignKey(
            'fk_ad_yandex_campaign_yandex_campaign', '{{%ad_yandex_campaign}}', 'yandex_campaign_id',
            '{{%yandex_campaign}}', 'id', 'cascade'
        );

        $this->addForeignKey(
            'fk_ad_yandex_campaign_template', '{{%ad_yandex_campaign}}', 'template_id', '{{%template}}', 'id', 'cascade'
        );
    }

    public function safeDown()
    {
    }

}
