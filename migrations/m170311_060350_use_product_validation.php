<?php

use yii\db\Migration;

class m170311_060350_use_product_validation extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'is_link_validation', $this->boolean()->comment('Использовать валидацию ссылок'));
        $this->renameColumn('shop', 'is_use_schedule', 'is_import_schedule');
        $this->addCommentOnColumn('shop', 'is_import_schedule', 'Использовать импорт по расписанию');
    }

    public function down()
    {
        $this->dropColumn('shop', 'is_link_validation');
        $this->renameColumn('shop', 'is_import_schedule', 'is_use_schedule');
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
