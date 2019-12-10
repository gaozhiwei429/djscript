<?php
/**
 * 党员相关接口请求入口操作
 * @文件名称: DangyuanController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\DangyuanService;
use appcomponents\modules\common\OrganizationService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class DangyuanController extends  UserBaseController
{
    /**
     * 用户登录态基础类验证
     * @return array
     */
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 数据列表获取
     * @return array
     */
    public function actionGetList() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $dangyuanService = new DangyuanService();
        $params = [];
        $p = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $organization_uuid = trim(Yii::$app->request->post('organization_uuid', null));
        if(empty($organization_uuid)) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $params[] = ['=', 'uuid', $organization_uuid];
        $organizationService = new OrganizationService();
        $organizationInfoRet = $organizationService->getInfo($params);
        if(!BaseService::checkRetIsOk($organizationInfoRet)) {
            return BaseService::returnErrData([], 54500, "请求参数异常");
        }
        $organizationInfo = BaseService::getRetData($organizationInfoRet);
        $organization_id = isset($organizationInfo['id']) ? $organizationInfo['id'] : 0;
        if(!$organization_id) {
            return BaseService::returnErrData([], 55000, "请求参数异常");
        }
        $dangyuanParams = [];
        $dangyuanParams[] = ['=', 'organization_id', $organization_id];
        $dangyuanParams[] = ['=','status',1];
        return $dangyuanService->getList($dangyuanParams, ['id'=>SORT_DESC], $p, $size,['*']);
    }

    /**
     * 数据详情获取
     * @return array
     */
    public function actionGetInfo() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $dangyuanService = new DangyuanService();
        $params = [];
        $id = intval(Yii::$app->request->post('id', 0));
        if(empty($id)) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $params[] = ['=', 'id', $id];
        return $dangyuanService->getInfo($params);
    }
}
