<?php
/**
 * 党费缴纳相关相关的接口
 * @文件名称: PaidUpController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\OrganizationService;
use appcomponents\modules\common\PaidUpService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class PaidUpController extends UserBaseController
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
        $newsService = new PaidUpService();
        $params = [];
        return $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,['*']);
    }

    /**
     * 详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $newsService = new PaidUpService();
        $params[] = ['=', 'id', $id];
        return $newsService->getInfo($params);
    }

    /**
     * 党费缴纳提交
     * @return array
     */
    public function actionSubmit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $year = intval(Yii::$app->request->post('year', 0));
        $month = intval(Yii::$app->request->post('month', 0));
        $organization_id = intval(Yii::$app->request->post('organization_id', 0));
        $dataInfo = [];
        if(empty($organization_id)) {
            return BaseService::returnErrData([], 514100, "请选择党组织");
        } else {
            $organizationService = new OrganizationService();
            $organizationParams = [];
            $organizationParams[] = ['=', 'id', $organization_id];
            $organizationParams[] = ['!=', 'status', -1];
            $userOrganizationInfoRet = $organizationService->getInfo($organizationParams);
            if(!BaseService::checkRetIsOk($userOrganizationInfoRet)) {
                return BaseService::returnErrData([], 514800, "该党组织不存在，或已下线");
            }
        }
        $dataInfo['organization_id'] = $organization_id;
        if(!empty($year)) {
            $dataInfo['year'] = $year;
        } else {
            $dataInfo['year'] = 0;
        }
        if(!empty($month)) {
            $dataInfo['month'] = $month;
        } else {
            $dataInfo['month'] = 0;
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['user_id'] = $this->user_id;
        $mettingService = new PaidUpService();
        return $mettingService->editInfo($dataInfo);
    }
}
