<?php
/**
 * 基础controller
 * @文件名称: BaseController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-05
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\controllers;

use source\libs\DmpLog;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
use yii\base\Controller;

class BaseController extends Controller
{
    public function beforeAction($action) {
        try{
//            DmpLog::debug(Yii::$app->request->post());
            return parent::beforeAction($action);
        }catch (BaseException $e) {
            return BaseService::returnErrData([], 500, "请求数据异常");
        }
    }
    public function afterAction($action, $result) {
//        $result = $module->afterAction($action, $result);
        try{
            return parent::afterAction($action, $result);
        }catch (BaseException $e) {
            return BaseService::returnErrData([], 500, "请求数据异常");
        }
    }
    public static function responseError($data=[], $msg='', $code=500) {
        if(isset(Yii::$app->params['logFromEmail'])) {

        }
        DmpLog::error($msg, $data);
    }
    public static function responseOk($data=[], $successMsg='') {

    }
}
