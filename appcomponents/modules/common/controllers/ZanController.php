<?php
/**
 * 点赞相关相关的接口
 * @文件名称: ZanController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\ZanService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class ZanController extends UserBaseController
{
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 列表数据获取
     * @return array
     */
    public function actionGetList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $newsService = new ZanService();
        $params = [];
		if($utilization_flag) {
            $params[] = ['=', 'utilization_flag', $utilization_flag];
        }
        if($object_id) {
            $params[] = ['=', 'object_id', $object_id];
        }
        $params[] = ['!=', 'status', 0];
        return $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,['*']);
    }
    /**
     * 提交关注数据
     * @return array
     */
    public function actionSubmit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $feebackService = new ZanService();
        return $feebackService->commitInfo($this->user_id, $object_id, $utilization_flag, 1);
    }
    /**
     * 取消关注
     * @return array
     */
    public function actionCancel() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $feebackService = new ZanService();
        return $feebackService->commitInfo($this->user_id, $object_id, $utilization_flag, 0);
    }

    /**
     * 提交关注数据
     * @return array
     */
    public function actionCheck() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 59200, "暂无数据");
        }
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $feebackService = new ZanService();
        $zanParams[] = ['=', 'user_id', $this->user_id];
        $zanParams[] = ['=', 'object_id', $object_id];
        $zanParams[] = ['=', 'utilization_flag', $utilization_flag];
        $zanParams[] = ['=', 'status', 1];
        $ret = $feebackService->getInfo($zanParams);
        if(BaseService::checkRetIsOk($ret)) {
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData([], 510700, "暂无关注数据");
    }
}
