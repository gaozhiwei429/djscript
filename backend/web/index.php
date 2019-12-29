<?php
/**
 * 入口文件
 * @文件名称: index.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
//允许跨域设置
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers:Authorization');
header("Access-Control-Allow-Methods: GET, POST, OPTION, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization,userid,token,sign,source,lang,version");

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('LOG_FILE_PATH') or define('LOG_FILE_PATH', './runtime/3d-api-script/data/logs');
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',10);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('DMP_IDENTITY') or define('DMP_IDENTITY', 'web');
//defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../config/common/bootstrap.php');

$config = require(__DIR__ . '/../config/'.YII_ENV.'/web.php');
$application=new yii\web\Application($config);
$application->run();