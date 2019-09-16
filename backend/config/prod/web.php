<?php

/**
 * 引用文件相关的配置
 * @文件名称: web.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
$commonWebConfig = require(dirname(__DIR__) . '/common/web.php');

$config = [
    'components' => [
        'session' => [
            'class' => 'yii\redis\Session',
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'redis'     => require(__DIR__ . '/redis.php'),
        'db'        => require(__DIR__ . '/db.php'),
        'mailer'        => require(__DIR__ . '/mailer.php'),
    ],
    'params' => require(__DIR__ . '/params.php'),
];
//开启dev环境的debug如果想关闭将这段代码的if条件取反即可！
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
return \yii\helpers\ArrayHelper::merge($commonWebConfig, $config);