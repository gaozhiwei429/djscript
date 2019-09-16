<?php
/**
 * @文件名称: DmpUtil.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;

use Yii;
use source\manager\BaseException;
use yii\helpers\Html;

class DmpUtil
{
    private static $except = [
        'upload_file', 'perm_ids', 'role_ids', 'imgs', 'changeList', 'fw_activity_array', 'banner_array', 'undefined', 'tag_identifier_desc'
    ];

    /**
     * @param $result string
     */
    public static function dump($result)
    {
        if(is_array($result)){
            $result = json_encode($result);
        }

        echo '[ ' .  DmpUtil::getDateBy(time()) . ' ] ' . $result . PHP_EOL;
    }

    public static function getDay($time = '')
    {
        if (empty($time)) {
            $time = time();
        }

        return date('Y-m-d', $time);
    }

    public static function dumpTs()
    {
        echo date('Y-m-d H:i:s', time()) . PHP_EOL;
    }

    /**
     * 通过时间戳获得字符串日期
     */
    public static function getDateBy($time = '')
    {
        if ($time == 0) {
            return 'N/A';
        }

        if (empty($time)) {
            $time = time();
        }
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 通用检查controller.action.result是否是true
     */
    public static function isResultTrue($result)
    {
        if ($result != true) {
            throw new BaseException('result is false in DmpUtil.isResultTrue', 50034);
        }
    }

    /**
     * 手机号校验
     */
    public static function isMobile($mobile)
    {
        if (is_numeric($mobile) && preg_match('/^13[0-9]{9}$|^14[0-9]{9}$|^15[0-9]{9}$|^17[0-9]{9}$|^18[0-9]{9}$/', $mobile)) {
            return true;
        }
        return false;
    }

    /**
     * 初始化时区
     */
    public static function initTimeZone()
    {
        date_default_timezone_set(Yii::$app->params['defaultTimeZone']);
    }

    public static function initPHPCommandConfig()
    {
        ini_set('memory_limit', Yii::$app->params['memoryLimit']);
        ini_set('max_execution_time', Yii::$app->params['maxExecutionTime']);
        ini_set('set_time_limit', 0);
    }

    public static function initPHPUploadConfig()
    {
        ini_set('post_max_size', Yii::$app->params['maxPostSize']);
        ini_set('upload_max_filesize', Yii::$app->params['maxUploadFileSize']);
    }

    public static function initHeader()
    {
        header("STrace: " . self::getHostName());

        if (YII_ENV == 'dev') {
            //            header("Access-Control-Allow-Origin: http://100.73.13.17");
//            header("Access-Control-Allow-Origin: http://100.66.166.51");
//            header("Access-Control-allow-credentials: true");
            header("Access-Control-Allow-Origin: *");
        }

    }

    /**
     * 过滤请求
     *
     * @param $source string type of source
     * @return bool
     */
    public static function filterRequest($source = null)
    {
        if ($source == 'client') {
            foreach (Yii::$app->request->post() as $k => $v) {
                Yii::$app->request->post()[$k] = trim(Html::encode($v));
            }

            foreach (Yii::$app->request->get() as $k => $v) {
                Yii::$app->request->get()[$k] = trim(Html::encode($v));
            }
        } else {
            foreach (Yii::$app->request->post() as $k => $v) {
                if (in_array($k, self::$except)) {
                    continue;
                }
                if(!is_array($v)) {
                    Yii::$app->request->post()[$k] = trim(Html::encode($v));
                } else {
                    Yii::$app->request->post()[$k] = $v;
                }
            }

            foreach (Yii::$app->request->get() as $k => $v) {
                if (in_array($k, self::$except)) {
                    continue;
                }
                Yii::$app->request->get()[$k] = trim(Html::encode($v));
            }
        }

        return true;
    }

    /**
     * 检查邮件格式
     *
     * TODO
     */
    public static function isEmail($email)
    {
        if (!$email) {
            throw new BaseException('email is invalid', 40388);
        }

        return true;
    }

    public static function isInt($int, $checkPositive = false)
    {
        if (!is_numeric(intval(trim($int)))) {
            return false;
        }

        if ($checkPositive == true && $int < 0) {
            return false;
        }

        return true;
    }

    public static function issetEmptyCheck($data, $defaultReturn = '')
    {
        return isset($data) && !empty($data) ? $data : $defaultReturn;
    }

    public static function issetEmptyCheckBool($data)
    {
        return isset($data) && !empty($data) ? true : false;
    }


    /**
     * 通过列名对一个数组排序
     */
    public static function arraySortByKey($array, $col_name, $asc = true)
    {
        if (count($array) <= 1) {
            return $array;
        }
        $index = [];
        foreach ($array as $k=>$v) {
            $index[] = $v[$col_name];
        }

        if ($asc === true) {
            $order = SORT_ASC;
        } elseif ($asc === false) {
            $order = SORT_DESC;
        } else {
            throw new BaseException('Invalid sort method in DmpUtil', 50016074);
        }
        array_multisort($index, $order, $array);
        return $array;
    }

    /**
     * ? TODO
     *
     * @param $data
     * @param $orderBy
     * @param bool|false $orderByDesc
     * @return bool
     */
    public static function filter(&$data, $orderBy, $orderByDesc = false)
    {
        $sortArray = [];
        foreach ($data as $key => $value) {
            if (!empty($orderBy)) {
                $sortArray[$key] = $value[$orderBy];
            }
        }

        if (!empty($sortArray)) {
            if ($orderByDesc) {
                return array_multisort($sortArray, SORT_DESC, SORT_REGULAR, $data);
            }
            return array_multisort($sortArray, SORT_ASC, SORT_REGULAR, $data);
        } else {
            return true;
        }
    }
    /**
     * 过滤Emoji表情编码
     */
    public static function filterEmoji($str)
    {
        $tmpStr = json_encode($str);
        $tmpStr = preg_replace("/(\\\u[ed][0-9a-f]{3})/i", " ", $tmpStr);
        return json_decode($tmpStr, true);
    }
    public static function utf8_strlen($string = null)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);

        // 返回单元个数
        return count($match[0]);
    }
    /**
     * 通过图片URL获取图片类型
     *
     * @param $url
     * @return mixed|string
     */
    public static function getType($url)
    {
        $data = explode('.', $url);
        $type = end($data);
        if ($type == 'jpg' || $type == 'jpeg') {
            return 'jpeg';
        }

        return $type;
    }

    /**
     * 通过URL获取图片的名字
     *
     * @param $url
     * @return mixed
     */
    public static function getUrlName($url)
    {
        $data = explode('/', $url);

        return end($data);
    }
    public static function getHostName()
    {
        return gethostname();
    }
    /**
     * 判断端版本号
     *
     * @param $currentVersion
     * @param $newestVersion
     * @return bool
     */
    public static function isCurrentVersionSmall($currentVersion, $newestVersion)
    {
        $cvList = explode('.',$currentVersion);
        $nvList = explode('.',$newestVersion);
        $cvLen = count($cvList);
        $nvLen = count($nvList);
        $len = max($cvLen,$nvLen);

        for($i=0;$i<$len;$i++){
            //对于不等长的版本号需要补零比较
            if(!isset($cvList[$i])){
                $cvList[$i] = '0';
            }
            if(!isset($nvList[$i])){
                $nvList[$i] = '0';
            }

            if($nvList[$i]>$cvList[$i]){
                return true;
            }elseif($nvList[$i]<$cvList[$i]){
                return false;
            }
        }

        return false;
    }
    /**
     * 前端传来的end_date是 Y-m-d 00:00:00, 变成 Y-m-d 23:59:59
     *
     * @param $endDate int
     * @return mixed
     */
    public static function processEndDate($endDate)
    {
        return $endDate + 24 * 3600 - 1;
    }
}
