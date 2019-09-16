<?php

/**
 * 数据库配置
 * @文件名称: db.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=dangjian;port=3306',
    'username' => 'root',
    'password' => '',
    'tablePrefix' => 'wbl_',
    'charset' => 'utf8',
];