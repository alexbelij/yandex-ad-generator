<?php

use yii\db\Migration;

class m161016_023114_account_id extends Migration
{
    public function up()
    {
        $this->addColumn('{{%ad_yandex_campaign}}', 'account_id', $this->integer());
        $this->addForeignKey('fk_ad_yandex_campaign_account_id_account', 'ad_yandex_campaign', 'account_id',
            'account', 'id');

        $this->addColumn('{{%yandex_campaign}}', 'account_id', $this->integer());
        $this->addForeignKey('fk_yandex_campaign_account_id_account', '{{%yandex_campaign}}', 'account_id', '{{%account}}', 'id');

        /** @var \app\models\Shop $shop */
        foreach (\app\models\Shop::find()->all() as $shop) {
            $account = new \app\models\Account([
                'title' => $shop->name,
                'token' => $shop->yandex_access_token,
                'account_type' => 'yandex',
                'account_data' => [
                    'yandex_application_id' => $shop->yandex_application_id,
                    'yandex_secret' => $shop->yandex_secret
                ]
            ]);
            if (!$account->save()) {
                throw new Exception('Error on account save');
            }

            $shop->account_id = $account->id;
            if (!$shop->save()) {
                throw new Exception('Error on shop save');
            }
            $sql = 'UPDATE ad_yandex_campaign
                    INNER JOIN ad ON ad.id = ad_yandex_campaign.ad_id
                    INNER JOIN product ON product.id = ad.product_id
                      SET account_id=:account_id
                    WHERE product.shop_id = :shop_id';

            Yii::$app->db->createCommand($sql, [':shop_id' => $shop->id, ':account_id' => $shop->account_id])->execute();

            $sql = 'UPDATE yandex_campaign SET account_id = :account_id WHERE shop_id=:shop_id';
            Yii::$app->db->createCommand($sql, [':account_id' => $account->id, ':shop_id' => $shop->id])->execute();
        }
    }

    public function down()
    {
        $this->dropForeignKey('fk_ad_yandex_campaign_account_id_account', 'ad_yandex_campaign');
        $this->dropColumn('{{%ad_yandex_campaign}}', 'account_id');

        $this->dropForeignKey('fk_yandex_campaign_account_id_account', '{{%yandex_campaign}}');
        $this->dropColumn('{{%yandex_campaign}}', 'account_id');

        \app\models\Shop::updateAll(['account_id' => null]);
        \app\models\Account::deleteAll();
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
