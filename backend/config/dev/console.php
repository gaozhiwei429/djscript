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
$commonConsoleConfig = require(dirname(__DIR__) . '/common/console.php');

$config = [
    'components' => [
        'db' => require(__DIR__ . '/db.php'),
        'redis'     => require(__DIR__ . '/redis.php'),
    ],
    'params' => require(__DIR__ . '/params.php'),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return \yii\helpers\ArrayHelper::merge($commonConsoleConfig, $config);