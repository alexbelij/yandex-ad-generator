<?php

use app\modules\bidManager\BidManagerModule;

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'timezone' => 'Europe/Moscow',
    'bootstrap' => ['log', '\app\components\Bootstrap'],
    'timeZone' => 'UTC',
    'name' => 'Yandex.Direct',
    'language' => 'ru-RU',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'dGRk46iOdPCE5d6DZzsRXaNauhBwzUDZ',
            'baseUrl' => '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['login']
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require("routes.php"),
        ],

        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
        ],

        'phpMorphy' => [
            'class' => '\app\components\PhpMorphy',
            'options' => [
                'storage' => phpMorphy::STORAGE_FILE
            ],
            'dictPath' => '@app/phpmorphy/dicts'
        ],

    ],
    'modules' => [
        'bid-manager' => [
            'class' => BidManagerModule::class,
        ],
        'feed' => [
            'class' => \app\modules\feed\FeedModule::class
        ]
    ],
    'aliases' => [
        '@bidManager' => '@app/modules/bidManager',
        '@feed' => '@app/modules/feed'
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
