<?php

use yii\db\Migration;

class m170215_124605_variation_item extends Migration
{
    public function up()
    {
        $this->createTable('variation_item', [
            'id' => $this->primaryKey(),
            'variation_id' => $this->integer()->comment('Вариация'),
            'value' => $this->string(1024)->comment('Текст вариации'),
            'is_use_in_generation' => $this->boolean()->comment('Использовать вариацию при генерации заголовков')->defaultValue(true)
        ]);

        $this->addForeignKey(
            'fk_variation_item_variation',
            'variation_item',
            'variation_id',
            'variation',
            'id',
            'cascade'
        );

        $variations = Yii::$app->db->createCommand('Select * from variation')->queryAll();

        foreach ($variations as $variation) {
            foreach (\app\helpers\StringHelper::explodeByDelimiter($variation['variation']) as $value) {
                $this->insert('variation_item', [
                    'value' => $value,
                    'variation_id' => $variation['id'],
                    'is_use_in_generation' => true
                ]);
            }
        }
    }

    public function down()
    {
        $this->dropTable('variation_item');
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
