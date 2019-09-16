<?php

/**
 * mailer邮箱相关的配置
 * @文件名称: mailer.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
return [
    'class' => 'yii\swiftmailer\Mailer',
//    'viewPath' => '@mail/common',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.163.com',
        'username' => '15910987706@163.com',
        'password' => 'wbaole.com',
        'port' => '465/994',
        'encryption' => 'ssl',
    ],
    'messageConfig'=>[
        'charset'=>'UTF-8',
        'from'=>['15910987706@163.com'=>'wbaole.com']
    ],
];