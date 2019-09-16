<?php

namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\CommonService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\libs\DmpLog;
use source\manager\BaseService;
use Yii;
/**
 * 文件上传接口
 */
class UploadController extends UserBaseController
{
    public function beforeAction($action){
        $userToken = parent::userToken();
        if (!BaseService::checkRetIsOk($userToken)) {
            return $userToken;
        }
        return parent::beforeAction($action);
    }
    /**
     * 腾讯cos文件上传
     * @return array
     */
    public function actionImages() {
        $user_id = 11;
//        if (Yii::$app->request->isPost) {
            $commonService = new CommonService();
//            $ret = $commonService->uploadImg($user_id, $_FILES);
            $local_path = "";
            $key = "";
            if(isset($_FILES['files']['name']) && !empty($_FILES['files']['name'])) {
                $key = $_FILES['files']['name'];
            }
            if(isset($_FILES['files']['tmp_name']) && !empty($_FILES['files']['tmp_name'])) {
                $local_path = $_FILES['files']['tmp_name'];
            }
            if(!empty($local_path) && !empty($key)) {
                $ret = $commonService->uploadTencentCos($user_id, $local_path, $key);
                return $ret;
            }
//        }
        return BaseService::returnErrData([], 500, '提交方式异常');
    }
    /**
     * 阿里云oss文件上传
     * @return array
     */
    public function actionAliFile() {
        DmpLog::debug($_FILES);
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
//        if (Yii::$app->request->isPost) {
        $commonService = new CommonService();
//            $ret = $commonService->uploadImg($user_id, $_FILES);
        $local_path = "";
        $key = "";
        if(isset($_FILES['files']['name']) && !empty($_FILES['files']['name'])) {
            $keyArr = explode('.', $_FILES['files']['name']);
            $key = Common::getRandChar(10).".".(isset($keyArr[1]) ? $keyArr[1] : "jpg");
        }
        if(isset($_FILES['files']['tmp_name']) && !empty($_FILES['files']['tmp_name'])) {
            $local_path = $_FILES['files']['tmp_name'];
        }
        if(!empty($local_path) && !empty($key)) {
            $ret = $commonService->uploadAlioss($this->user_id, $local_path, $key);
            return $ret;
        }
        return BaseService::returnErrData([], 56900, '提交方式异常');
    }
}
