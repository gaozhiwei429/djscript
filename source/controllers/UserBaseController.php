<?php
/**
 * base controller
 * @文件名称: UserBaseController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\controllers;

use appcomponents\modules\passport\PassportService;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;

class UserBaseController extends BaseController
{
    public $user_id;
    public $noLogin = true;
    public function beforeAction($action) {
        try{
            if($this->noLogin) {
                $userToken = $this->userToken();
                if(!BaseService::checkRetIsOk($userToken)) {
                    return $userToken;
                }
                if(empty($this->user_id)) {
                    return BaseService::returnErrData('', 5001, "登陆状态已失效");
                }
            }
            return parent::beforeAction($action);
        }catch (BaseException $e) {
            return BaseService::returnErrData([], 5001, "请求数据异常");
        }
    }
    /**
     * 基类用户header验证
     * @return array
     */
    public function userToken() {
        // $headers 是一个 yii\web\HeaderCollection 对象
        $headers = Yii::$app->request->headers;
        // 返回 Accept header 值
        $user_id = $headers->get('userid', Yii::$app->request->post('userid', 0));
        $token = $headers->get('token', Yii::$app->request->post('token', null));
        $sign = $headers->get('sign', Yii::$app->request->post('sign', null));
        $type = $headers->get('type', Yii::$app->params['user']['type']);
        if(empty($user_id) || empty($token) || empty($sign) || empty($type)) {
            return BaseService::returnErrData([], 5001, "请求参数异常");
        }
        $passportService = new PassportService();
        $verifyToken = $passportService->verifyToken($user_id, $token, $sign, $type);
        if(!BaseService::checkRetIsOk($verifyToken)) {
            return $verifyToken;
        }
        $this->user_id = $user_id;
        return BaseService::returnOkData([]);
    }
}
