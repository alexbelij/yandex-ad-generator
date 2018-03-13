<?php

namespace app\models;

use app\behaviors\ModelJsonFieldsBehavior;
use app\helpers\ArrayHelper;

/**
 * Class YandexAccount
 * @package app\models
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
                'field' => 'account_data'
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
