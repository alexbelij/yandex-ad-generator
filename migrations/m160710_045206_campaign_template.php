<?php

use yii\db\Migration;

class m160710_045206_campaign_template extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%campaign_template}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'shop_id' => $this->integer(),
            'regions' => $this->string(),
            'negative_keywords' => $this->text(),
            'text_campaign' => $this->text()
        ]);

        $this->addForeignKey(
            'fk_campaign_template_shop',
            '{{%campaign_template}}',
            'shop_id',
            '{{%shop}}',
            'id',
            'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%campaign_template}}');
    }

}
