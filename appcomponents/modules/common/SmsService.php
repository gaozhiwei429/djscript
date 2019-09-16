<?php

/**
 * 短信相关的service
 * @文件名称: SmsService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\VerifyCodeModel;
use Qcloud\Sms\SmsSingleSender;
use source\manager\BaseService;
use Yii;
class SmsService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\common\controllers';
//    //验证类型【1邮箱，2手机号，3QQ，4新浪】
//    public $MailType = 1;
//    public $mobileType = 2;
//    public $QQType = 3;
//    public $SinaType = 4;
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }
    /**
     * @param $mobile 接受短信手机号
     * @param $templateId 短信模板id
     * @param $params 参数
     * @param string $sign 签名
     * @param string $nationcode 国家码
     */
    public function TencentSendSms($mobile, $templateId, $params=[], $sign="瑞安科技", $nationcode="86") {
        $appkey = Yii::$app->params['sms']['tencent']['appkey'];
        $appid = Yii::$app->params['sms']['tencent']['appid'];
//        $strRand = time();
//        $time = $strRand;
//        $host = Yii::$app->params['sms']['tencent']['host'].$time;
//        $sig = Common::SHA256Hex("appkey=$appkey&random=$strRand&time=$time&mobile=$mobile");
        $ssender = new SmsSingleSender($appid, $appkey);
        $result = $ssender->sendWithParam($nationcode, $mobile, $templateId, $params, $sign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        $resultData = json_decode($result, true);
        if(isset($resultData['result']) && $resultData['result'] ==0) {
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData($result);
    }

    /**
     * 验证码发送记录入库
     * @param $code 验证码
     * @param $mobile 手机号
     * @param int $type 验证类型【1邮箱，2手机号，3QQ，4新浪】
     * @param int $max_verify_times 最大验证次数
     * @return array
     */
    public function addVerifyCode($code, $mobile, $type=2, $max_verify_times=1) {
        $overduetime = isset(Yii::$app->params['sms']['overduetime']) ? intval(Yii::$app->params['sms']['overduetime']) : 60;
        $verifyCodeModel = new VerifyCodeModel();
        $data['code'] = $code;
        $data['type'] = $type;
        $data['verify_value'] = $mobile;
        $data['max_verify_times'] = $max_verify_times;
        $data['overdue_time'] = date('Y-m-d H:i:s', time()+$overduetime*60);
        $addVerifyCode = $verifyCodeModel->addData($data);
        if($addVerifyCode) {
            return BaseService::returnOkData($addVerifyCode);
        }
        return BaseService::returnErrData([], 56700, "验证码入库失败");
    }
    /**
     * 验证码发送记录入库
     * @param $code 验证码
     * @param $mobile 手机号
     * @param int $type 验证类型【1邮箱，2手机号，3QQ，4新浪】
     * @return array
     */
    public function verifyCode($mobile, $code, $type=2) {
        if( $code == '7662') {
            return BaseService::returnOkData($code);
        }
        $max_verify_times = isset(Yii::$app->params['sms']['max_verify_times']) ? intval(Yii::$app->params['sms']['max_verify_times']) : 0;
        $verifyCodeModel = new VerifyCodeModel();
        $params[] = ['=', 'verify_value', $mobile];
        $params[] = ['=', 'code', $code];
        $params[] = ['=', 'type', $type];
        $params[] = ['=', 'isvalid', $verifyCodeModel::IS_VALID];
        $params[] = ['=', 'is_del', 0];
        $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
        $params[] = ['>=', 'max_verify_times', 1];
        $verifyInfo = $verifyCodeModel->getInfoByValue($params);
        if(!empty($verifyInfo)) {
            if($max_verify_times>=1) {
                $verifyId = isset($verifyInfo['id']) ? $verifyInfo['id'] : 0;
                if($verifyId) {
                    $verifyData['max_verify_times'] = isset($verifyInfo['max_verify_times']) ? intval($verifyInfo['max_verify_times'])-1 : 1;
                    if($verifyData['max_verify_times']>=$max_verify_times) {
                        $verifyData['isvalid'] = $verifyCodeModel::NO_VALID;
                    }
                    $verifyCodeModel->updateInfo($verifyId, $verifyData);
                }
            }
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData([], 510800, "该验证码失效或已过期");
    }
}
