<?php

$config = [
    'id' => '3D-eshop-manage',
    'controllerNamespace' => 'backend\controllers',
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . '/vendor',
    'basePath' => dirname(dirname(__DIR__)),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Shanghai',//时区设置
    'modules' => [
        'passport' => [
            'class' => 'appcomponents\modules\passport\PassportService',
        ],
        'common' => [
            'class' => 'appcomponents\modules\common\CommonService',
        ],
        'project' => [
            'class' => 'appcomponents\modules\project\ProjectService',
        ],
        'order' => [
            'class' => 'appcomponents\modules\order\OrderService',
        ],
        'pay' => [
            'class' => 'appcomponents\modules\pay\PayService',
        ],
        'my' => [
            'class' => 'appcomponents\modules\my\MyService',
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'dmpisthebest',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
//        'cache' => [
//            'class' => 'yii\caching\FileCache',
//        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'mailer' => require(__DIR__ . '/mailer.php'),
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                [
                    'class'        => 'yii\log\FileTarget',
                    'categories' => ['user','customer'],
                    'logFile' => LOG_FILE_PATH,
                    'levels' => ['info', 'error', 'warning'],
                    'logVars' => [],
                    'exportInterval' => 100,
                    'maxFileSize' => 4096000,//4096000,//文件大小的字节数
//                    'maxLogFiles' => 365,
                    'rotateByCopy' => false,
                    'fileMode' => 0777,
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'rules' => require(__DIR__ . '/rules.php'),
        ],
        'cos'=>[
            'class'=>'xplqcloud\cos\Cos',
            'app_id' => '1258502489',
            'secret_id' => 'AKIDHLFZUA4zTlQYcYnG9vYKm76mYGl83Auf',
            'secret_key' => 'xb1C6CVcT4B9tS2QZaDeWrCRUBWtugEn',
            'region' => 'bj',
            'bucket'=>'wbaole-1258502489',
            'insertOnly'=>true,
            'timeout' => 200
        ],
//        'response' => [
//            'format' => yii\web\Response::FORMAT_JSON,
//            'charset' => 'UTF-8',
//        ],
    ],
];

return $config;
