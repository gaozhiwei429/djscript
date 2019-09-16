<?php

/**
 * console配置
 * @文件名称: console.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
Yii::setAlias('@tests', dirname(dirname(__DIR__)) . '/tests/codeception');

$config = [
    'id' => 'framework-console',
    'basePath' => dirname(dirname(__DIR__)),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'commands',
    'timeZone' => 'Asia/Shanghai',//时区设置
    'modules' => [
        'passport' => [
            'class' => 'appcomponents\modules\passport\PassportService',
        ],
        'common' => [
            'class' => 'appcomponents\modules\common\CommonService',
        ]
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                [
                    'class'        => 'yii\log\FileTarget',
                    'logFile'      => './runtime/logs/' . date('Ymd') . '/' . date('H') . '.log',
                    'levels'       => ['error', 'warning', 'info'],
                    'maxFileSize'  => '1024000',
                    'rotateByCopy' => false
                ],
            ],
        ],
        'mailer'      => [
            'class'     => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'smtp.exmail.qq.com',
                'username'   => '',
                'password'   => '',
                'port'       => '465',
                'encryption' => 'ssl',
            ],
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;