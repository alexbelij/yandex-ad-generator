<?php

namespace app\modules\bidManager\models\accounts;

use app\behaviors\ModelJsonFieldsBehavior;
use app\modules\bidManager\models\Account;

/**
 * Class YandexAccount
 * @package app\modules\bidManager\models\accounts
 */
class YandexAccount extends Account
{
    /**
     * @var string
     */
    public $yandexApplicationId;

    /**
     * @var string
     */
    public $yandexSecret;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ModelJsonFieldsBehavior::class,
                'modelFields' => [
                    'yandexApplicationId',
                    'yandexSecret'
                ],
                'field' => 'settings'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['yandexApplicationId', 'yandexSecret'], 'string']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'yandexApplicationId' => 'Yandex application id',
            'yandexSecret' => 'Yandex secret'
        ]);
    }
}
