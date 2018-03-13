<?php

use app\modules\bidManager\BidManagerModule;

Yii::setAlias('@tests', dirname(__DIR__) . '/tests/codeception');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'timezone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', '\app\components\Bootstrap'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'logger' => [
            'class' => 'app\components\ConsoleLogger'
        ],
        'db' => $db,

        'phpMorphy' => [
            'class' => '\app\components\PhpMorphy',
            'options' => [
                'storage' => phpMorphy::STORAGE_FILE
            ],
            'dictPath' => '@app/phpmorphy/dicts'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => !empty($params['baseUrl']) ? $params['baseUrl'] : 'http://127.0.0.1/',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'transport' => [
                'class' => Swift_SmtpTransport::class,
                'host' => 'smtp.yandex.ru',
                'username' => 'ad.generator',
                'password' => 'CegthGfhjkm01',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
    ],
    'modules' => [
        'bid-manager' => [
            'class' => BidManagerModule::class,
        ],
        'feed' => [
            'class' => \app\modules\feed\FeedModule::class,
        ]
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
