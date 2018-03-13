<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.09.16
 * Time: 8:26
 */

namespace app\models\parts;

use yii\base\Model;

class ShopSchedule extends Model
{
    public $time;

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'time' => 'Время запуска'
        ];
    }
}
