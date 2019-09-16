<?php
/**
 * 所有service层应有的base继承
 * @文件名称: BaseService.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */

namespace source\manager;
use source\libs\DmpLog;
use \Yii;

class BaseService extends \yii\base\Module
{
    public function __construct(){
        try{
        }catch (BaseException $e) {
            DmpLog::error('base_service_error', $e);
            return BaseService::returnErrData([], 500, "请求数据异常");
        }
    }
    /**
     * 正确数据的返回
     * @param $data
     * @return array
     */
    public static function returnOkData($data, $msg=null) {
        @header('Content-Type:application/json; charset=utf-8');//返回json数据格式而不是json字符串
        $return = [
            'code' => 0,
            'msg' => $msg ? $msg : 'success',
            'data' => $data,
        ];
        $return = json_encode($return);
        return $return;
    }

    /**
     * 异常情况数据的返回
     * @param $data
     * @param int $errno
     * @param string $errmsg
     * @return array
     */
    public static function returnErrData ($data, $errno = 500, $errmsg = 'fail') {
        @header('Content-Type:application/json; charset=utf-8');//返回json数据格式而不是json字符串
        $return = [
            'code' => (int)$errno,
            'msg' => $errmsg,
            'data' => $data,
        ];
        $return = json_encode($return, JSON_UNESCAPED_UNICODE);
        return $return;
    }

    /**
     * 检查返回值是否为正确的值
     * @param $return
     * @return bool
     */
    public static function checkRetIsOk($return) {
        if(!is_array($return)) {
            $return1 = json_decode($return, true);
            $jsonErr = json_last_error();
            if($jsonErr == 4) {
                $return = json_decode(trim($return,chr(239).chr(187).chr(191)),true);
            } else {
                $return = $return1;
            }
        }
        if(!isset($return['code']) || $return['code']!=0) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * json转化为数组
     * @param $jsonStr
     * @return mixed
     */
    public static function jsonToArr($jsonStr) {
        return json_decode($jsonStr, true);
    }
    /**
     * 获取返回值结果数据的data数据
     * @param $jsonStr
     * @return array
     */
    public static function getRetData($jsonStr) {
        $ret = json_decode($jsonStr, true);
        if(is_array($ret) && !empty($ret['data'])) {
            return $ret['data'];
        }
        return [];
    }
    /**
     * 获取返回值结果数据的code数据
     * @param $jsonStr
     * @return array
     */
    public static function getCodeData($jsonStr) {
        $ret = json_decode($jsonStr, true);
        if(is_array($ret) && !empty($ret['code'])) {
            return $ret['code'];
        }
        return 0;
    }
}
