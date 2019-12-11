<?php
/**
 * 组织相关的接口
 * @文件名称: OrganizationController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\OrganizationService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class OrganizationController extends  UserBaseController
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
     * 首页获取
     * @return array
     */
    public function actionGetList() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $parent_uuid = trim(Yii::$app->request->post('parent_uuid', "01403563-FEEE-11E9-9BC6-00163E30C149"));
        $organizationService = new OrganizationService();
        $params = [];
        if($parent_uuid) {
            $params[] = ['=', 'parent_uuid', $parent_uuid];
        }
        $params[] = ['=', 'status', 1];
        return $organizationService->getList($params, ['id'=>SORT_DESC,'sort'=>SORT_DESC], $page, $size,['uuid','title','parent_uuid','organization_type','branch_type']);
    }

    /**
     * 详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $uuid = trim(Yii::$app->request->post('uuid', null));
        if(empty($uuid)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $organizationService = new OrganizationService();
        $params = [];
        $params[] = ['=', 'uuid', $uuid];
        return $organizationService->getInfo($params);
    }
    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionGetTree() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $parent_uuid = trim(Yii::$app->request->post('parent_uuid', ""));
        $organizationService = new OrganizationService();
        $params = [];
        $params[] = ['!=', 'status', 0];
        return $organizationService->getTree($params, ['id'=>SORT_ASC], 1, -1,
            $fied=['uuid','title','parent_uuid','organization_type','branch_type','contacts'],
            true,$parent_uuid);
    }
}
