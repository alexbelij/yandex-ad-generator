<?php

use yii\db\Migration;

class m160709_150338_rename_tables extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->renameTable('{{%ads}}', '{{%ad}}');
        $this->renameTable('{{%products}}', '{{%product}}');
        $this->renameTable('{{%settings}}', '{{%setting}}');
        $this->renameTable('{{%shops}}', '{{%shop}}');
        $this->renameTable('{{%sitelinks}}', '{{%sitelink}}');
        $this->renameTable('{{%sitelinks_item}}', '{{%sitelink_item}}');
        $this->renameTable('{{%templates}}', '{{%template}}');
        $this->renameTable('{{%users}}', '{{%user}}');
        $this->renameTable('{{%variations}}', '{{%variation}}');
        $this->renameTable('{{%vcards}}', '{{%vcard}}');
    }

    public function safeDown()
    {
        $this->renameTable('{{%ad}}', '{{%ads}}');
        $this->renameTable('{{%product}}', '{{%products}}');
        $this->renameTable('{{%setting}}', '{{%settings}}');
        $this->renameTable('{{%shop}}', '{{%shops}}');
        $this->renameTable('{{%sitelink}}', '{{%sitelinks}}');
        $this->renameTable('{{%sitelink_item}}', '{{%sitelinks_item}}');
        $this->renameTable('{{%template}}', '{{%templates}}');
        $this->renameTable('{{%user}}', '{{%users}}');
        $this->renameTable('{{%variation}}', '{{%variations}}');
        $this->renameTable('{{%vcard}}', '{{%vcards}}');
    }

}
