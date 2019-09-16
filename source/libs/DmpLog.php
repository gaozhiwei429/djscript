<?php
/**
 * 日志操作统一入口
 * @文件名称: DmpLog.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;
use Yii;
class DmpLog
{
    /**
     * 将里面的元素都转化成字符串.
     **/
    public static function toStr($context)
    {
        try {
            if(is_object($context)) {
                $context = json_decode( json_encode( $context),true);
            }
            if(is_array($context)) {
                foreach ($context as $key => $value) {
                    if (is_array($value)) {
                        $context[$key] = self::toStr($value);
                    } else {
                        $context[$key] = $value;
                        //$context[$key] = strval($value);
                        //$context[$key] = strval($value);
                    }
                }
            }
        } catch (\ErrorException $e) {
            Yii::error(['msg' => $e->getMessage(), 'context' => "'" . json_encode($context) . "'"], 'trace_parse_error') ;
        }
        return $context;
    }
    /**
     *  记录追踪日志.
     *  @param  $message  string  记录的事件名字
     *  @param  $security string  事件的级别
     *  @param  $context  array  上下文
     *  @param  $category string  访问的路径
     **/
    public static function addLog($logfile, $message, $security, $content = array(), $category = 'default')
    {
        $trace = 'info';
        if(isset(Yii::$app->params['traceLevel'][$security]) && Yii::$app->params['traceLevel'][$security]) {
            $trace = Yii::$app->params['traceLevel'][$security];
        }
        static::defineLogID($category);
        $trackid = YII_LOG_ID;
        if ($category == 'default') {
            $category = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
        }
        $request = Yii::$app->request;
        $refer = '';
        if(method_exists($request,'getReferrer')){
            $refer = Yii::$app->request->getReferrer();
        }
        $response = Yii::$app->response;
        $statusCode = '';
        if(isset(Yii::$app->response->statusCode)){
            $statusCode = Yii::$app->response->statusCode;
        }
            $content = self::toStr($content);
        // 定义trackid , 方便跟踪请求日志链条
        $info = [
            'trackid' => $trackid,
            'product' => Yii::$app->id,
            'timestamp' => date('Y-m-d H:i:s'),
            'category' => $category,
            'topic' => $message,
            'server' => gethostname(),
            'content' => $content,
            'level' => $security,
            'trace' => $trace,
            'refer' => $refer,//页面refer
            'httpcode' => $statusCode,//http请求状态码
        ];
        $logContent = json_encode($info , JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $logfile = self::fileToFile($logfile);
        if (!file_exists($logfile)) {
            file_put_contents($logfile, $logContent, FILE_APPEND | LOCK_EX);
            chmod($logfile, 0766);
        } else {
            file_put_contents($logfile, $logContent, FILE_APPEND | LOCK_EX);
        }
        /*if ($security == 'info') {
//            Yii::info($info, $category);
            Yii::info($info, $category);
        } elseif ($security == 'warning') {
            Yii::warning($info, $category);
        } elseif ($security == 'error') {
            Yii::error($info, $category);
        } else {
            //添加日志
            Yii::trace($info, $category);
        }*/
        return True;
    }

    /**
     * 业务日志的打印
     * @param $name
     * @param array $oldData 原数据
     * @param array $newData 改变后的数据
     */
    public static function operationLog($name, $newData=[], $oldData=[]) {
        $context = [
            'new_data' => $newData,
            'old_data' => $oldData,
        ];
        if(isset(Yii::$app->params['print_operation_log']) && Yii::$app->params['print_operation_log']) {
            $logdir = LOG_FILE_PATH."/operation"."/".date('Y-m-d');
            $logfile = $logdir."/".date("H").".log";
            if(!file_exists($logdir)) {
                @mkdir($logdir,0777,true);
            }
            self::addLog($logfile, $name, 'info', $context);
        }
    }
    /**
     * 常用日志调用统一入口
     *
     * @param $info
     * @param boolean $isError
     */
    public static function sqlLog($sql)
    {
        $logContent = $sql . PHP_EOL;
        $sqlLogdir = LOG_FILE_PATH."/sql"."/".date('Y-m-d');
        $sqlLogfile = $sqlLogdir."/".date("H").".sql";
        $sqlLogfile = self::fileToFile($sqlLogfile);
        if(!file_exists($sqlLogdir)) {
            if(@mkdir($sqlLogdir,0777,true)) {
                if (!file_exists($sqlLogfile)) {
                    @file_put_contents($sqlLogfile, $logContent, FILE_APPEND | LOCK_EX);
                    chmod($sqlLogfile, 0766);
                } else {
                    @file_put_contents($sqlLogfile, $logContent, FILE_APPEND | LOCK_EX);
                }
                return true;
            }
            return false;
        }
        @file_put_contents($sqlLogfile, $logContent, FILE_APPEND | LOCK_EX);
        return true;
    }

    /**
     * 定义info 级别trace 日志
     *
     * @return array
     **/
    public static function info($name, $context)
    {
        if(isset(Yii::$app->params['print_info_log']) && Yii::$app->params['print_info_log']) {
            $logdir = LOG_FILE_PATH."/info"."/".date('Y-m-d');
            $logfile = $logdir."/".date("H").".log";
            if(!file_exists($logdir)) {
                @mkdir($logdir,0777,true);
            }
            self::addLog($logfile, $name, 'info', $context);
        }
    }

    /**
     * 定义warning 级别trace 日志
     *
     * @return array
     **/
    public static function warning($name, $context)
    {
        $logdir = LOG_FILE_PATH."/warning"."/".date('Y-m-d');
        $logfile = $logdir."/".date("H").".log";
        if(!file_exists($logdir)) {
            @mkdir($logdir,0777,true);
        }
        self::addLog($logfile, $name, 'warning', $context);
    }

    /**
     * 定义 error 级别trace 日志
     *
     * @return array
     **/
    public static function error($name, $context)
    {
        $logdir = LOG_FILE_PATH."/error"."/".date('Y-m-d');
        $logfile = $logdir."/".date("H").".log";
        if(!file_exists($logdir)) {
            @mkdir($logdir,0777,true);
        }
        $logInfo = [
            'line' => $context->getLine(),
            'code' => $context->getCode(),
            'file' => $context->getFile(),
            'msg' => $context->getMessage(),
            'trace' => $context->getTrace(),
        ];
        self::addLog($logfile, $name, 'error', $logInfo);
    }
    /**
     * 记录日志到ELK
     *
     * ---------  Deprecate  ---------
     *
     * @param array $message 需要记录的日志
     * @param $level
     * @param string $category
     */
    public static function elkLog($message, $level, $category='default')
    {
        if ($category == 'default') {
            $category = Yii::$app->controller->id . '-' . Yii::$app->controller->action->id;
        }

        static::defineLogID();

        $data = [
            'LogID' => YII_LOG_ID,
            'server' => gethostname(),
            'message' => $message,
        ];
        if ($level == 'info') {
            Yii::info(json_encode($data, JSON_UNESCAPED_SLASHES), $category);
        } elseif ($level == 'error') {
            Yii::error(json_encode($data, JSON_UNESCAPED_SLASHES), $category);
        } elseif ($level == 'warning') {
            Yii::warning(json_encode($data, JSON_UNESCAPED_SLASHES), $category);
        } else {
            Yii::trace(json_encode($data, JSON_UNESCAPED_SLASHES), $category);
        }
    }

    public static function debug($data)
    {
        $data = self::toStr($data);
        static::defineLogID();

        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } else {
            $data = $data.PHP_EOL;
        }

        $filePath = LOG_FILE_PATH."/";//.'/debug/';
        if (! file_exists ( $filePath )) {
            @mkdir($filePath, 0777, true);
        }
        $file = $filePath.'debug-'.date("Y-m-d").'.log';
        $ts = '[ ' . DmpUtil::getDateBy(time()) . ' ] [debug-'.YII_LOG_ID.'] ';

        file_put_contents($file, $ts, FILE_APPEND);
        file_put_contents($file, $data, FILE_APPEND);
    }
    /**
     * 返回trace Id
     *
     * 保证 traceId 尽可能的 不同，即使面对高并发
     *
     * @return array
     * @author jawei
     **/
    public static function getTraceId($category = '')
    {
        if (defined('YII_TRACK_ID')) {
            return YII_TRACK_ID;
        }
        $str = microtime(true) . rand(0, 10000) . $category;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $str .= $_SERVER['HTTP_USER_AGENT'] ;
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $str .= $_SERVER['HTTP_REFERER'] ;
        }
        define('YII_TRACK_ID', md5($str));
        return YII_TRACK_ID;
    }

    public static function defineLogID($category = '')
    {
        defined('YII_LOG_ID') or define('YII_LOG_ID', md5(microtime().rand(0,10000) . $category));
    }
    /**
     * 日志文件字段生成
     * @param $file
     */
    public static function fileToFile($file) {
        $maxFileSize = isset(Yii::$app->components['log']['targets'][0]['maxFileSize']) ?
            Yii::$app->components['log']['targets'][0]['maxFileSize'] : 0;
        if($maxFileSize){
            //获取当前文件的目录地址
            $dir = dirname($file);
            $fileArr = explode($dir."/", $file);
            if (! file_exists ( $dir )) {
                @mkdir($dir, 0777, true);
            }
            $file = @scandir($dir);
            $valData = [];
            $ext = '';//文件扩展
            if(count($fileArr) ==2 ) {
                $ext = $fileArr[1];
            }
            if(!empty($file) && is_array($file)) {
                foreach($file as $k=>$v) {
                    $valArr = explode('.', $v);
                    $str = end($valArr);
                    if($str == 'log') {
                        $ext = '.'.$str;
                        $val = explode($ext, $v);
                        $valData[] = $val[0];
                    }
                    if($str == 'sql') {
                        $ext = '.sql';
                        $val = explode($ext, $v);
                        $valData[] = $val[0];
                    }
                }
            }

            $maxVal = '';
            if(!empty($valData)) {
                $maxVal = max($valData);//最后一个文件
            }
            $endFile = $maxVal.$ext;
            if(!file_exists($dir ."/". $endFile)) {
                @file_put_contents($dir ."/". $endFile, '');
            }
            $fileSize = @abs(@filesize($dir ."/". $endFile));
            if($fileSize >= $maxFileSize) {
                $endFileName = explode('.', $maxVal);
                $fileName = $endFileName[0];
                if(isset($endFileName[1])){
                    $endFile = $fileName.".".($endFileName[1]+1).$ext;
                } else {
                    $endFile = $fileName.".".count($endFileName).$ext;
                }
            }
            return $dir."/".$endFile;//文件扩展名
        }
        return $file;
    }
}