<?php

namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\SmsService;
use appcomponents\modules\passport\PassportService;
use source\controllers\BaseController;
use source\libs\Common;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
/**
 * 发送短信接口
 */
class SmsController extends BaseController
{
    public function beforeAction($action){
        return parent::beforeAction($action);
    }

    /**
     * 发送登录短信
     * @return array|void
     */
    public function actionLoginSend() {
        try {
            $mobile = trim(Yii::$app->request->post('mobile', null));
            if(empty($mobile)) {
                return BaseService::returnErrData([], 53000, "请求参数异常");
            }
            $passportService = new PassportService();
            $checkUserRet = $passportService->checkUserExist($mobile);
            if(!BaseService::checkRetIsOk($checkUserRet)) {
                return $checkUserRet;
            }
            $templateId = Yii::$app->params['sms']['tencent']['templateIdArr']['loginSymId'];
            $overduetime = Yii::$app->params['sms']['overduetime'];
            $max_verify_times = isset(Yii::$app->params['sms']['max_verify_times']) ? intval(Yii::$app->params['sms']['max_verify_times']) : 1;
            $code = Common::getRandChar(4, true);
            $smsService = new SmsService();
            $params[] = $code;
            $params[] = $overduetime;
            $ret = $smsService->TencentSendSms($mobile, $templateId, $params);
            if(BaseService::checkRetIsOk($ret)) {
                $smsService->addVerifyCode($code, $mobile, 2, $max_verify_times);
            }
            return $ret;
        } catch (BaseException $e) {
            DmpLog::error('sms_send_error', $e);
            return BaseService::returnErrData([], 400, '系统异常');
        }
    }
    /**
     * 发送操作短信
     * @return array|void
     */
    public function actionHandSend() {
        try {
            $mobile = trim(Yii::$app->request->post('mobile', null));
            if(empty($mobile)) {
                return BaseService::returnErrData([], 53000, "请求参数异常");
            }
            $templateId = Yii::$app->params['sms']['tencent']['templateIdArr']['handId'];
            $overduetime = Yii::$app->params['sms']['overduetime'];
            $max_verify_times = isset(Yii::$app->params['sms']['max_verify_times']) ? intval(Yii::$app->params['sms']['max_verify_times']) : 1;
            $code = Common::getRandChar(4, true);
            $smsService = new SmsService();
            $params[] = $code;
            $params[] = $overduetime;
            $ret = $smsService->TencentSendSms($mobile, $templateId, $params);
            if(BaseService::checkRetIsOk($ret)) {
                $smsService->addVerifyCode($code, $mobile, 2, $max_verify_times);
            }
            return $ret;
        } catch (BaseException $e) {
            DmpLog::error('sms_send_error', $e);
            return BaseService::returnErrData([], 400, '系统异常');
        }
    }
    /**
     * 验证短信验证码
     * @return array
     */
    public function actionVerify() {
        try {
            $mobile = trim(Yii::$app->request->post('mobile', null));
            $code = trim(Yii::$app->request->post('code', null));
            if(empty($mobile) || empty($code)) {
                return BaseService::returnErrData([], 500, '请求参数异常');
            }
            $smsService = new SmsService();
            return $smsService->verifyCode($mobile, $code);
        } catch (BaseException $e) {
            DmpLog::error('sms_verify_error', $e);
            return BaseService::returnErrData([], 400, '系统异常');
        }
    }

}
