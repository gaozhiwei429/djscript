<?php
/**
 * 活动相关相关的接口
 * @文件名称: ActivityController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\OrganizationService;
use appcomponents\modules\common\ActivityService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class ActivityController extends UserBaseController
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
        $newsService = new ActivityService();
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
        $newsService = new ActivityService();
        $params[] = ['=', 'id', $id];
        return $newsService->getInfo($params);
    }

    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetStatus() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $newsService = new ActivityService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['status'] = $status;
        return $newsService->editInfo($dataInfo);
    }

    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetSort() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = trim(Yii::$app->request->post('id', 0));
        $sort = intval(Yii::$app->request->post('sort',  0));
        $newsService = new ActivityService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['sort'] = $sort;
        return $newsService->editInfo($dataInfo);
    }
    /**
     * 详情数据编辑
     * @return array
     */
    public function actionEdit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $address = trim(Yii::$app->request->post('address', ""));
        $content = trim(Yii::$app->request->post('content', ""));
        $status = intval(Yii::$app->request->post('status', 0));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $organization_id = intval(Yii::$app->request->post('organization_id', 0));
        $startandenddate = trim(Yii::$app->request->post('startandenddate', null));
        $dataInfo = [];
        if(!empty($startandenddate)) {
            $startandenddateArr = explode(" - ", $startandenddate);
            if(!empty($startandenddateArr[0]) && isset($startandenddateArr[0])) {
                $dataInfo['start_time'] = $startandenddateArr[0];
            }
            if(!empty($startandenddateArr[1]) && isset($startandenddateArr[1])) {
                $dataInfo['end_time'] = $startandenddateArr[1];
            }
        }
        $mettingService = new ActivityService();
        if(empty($title)) {
            return BaseService::returnErrData([], 55900, "投票主题不能为空");
        }
        if(empty($content)) {
            return BaseService::returnErrData([], 55900, "投票选项不能为空");
        }
        if(empty($organization_id)) {
            return BaseService::returnErrData([], 55900, "请选择党组织");
        }
        $dataInfo = [];
        if(!empty($startandenddate)) {
            $startandenddateArr = explode(" - ", $startandenddate);
            if(!empty($startandenddateArr[0]) && isset($startandenddateArr[0])) {
                $dataInfo['start_time'] = $startandenddateArr[0];
            }
            if(!empty($startandenddateArr[1]) && isset($startandenddateArr[1])) {
                $dataInfo['end_time'] = $startandenddateArr[1];
            }
        }

        if(!empty($title)) {
            $dataInfo['title'] = $title;
        } else {
            $dataInfo['title'] = "";
        }

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
        if(!empty($address)) {
            $dataInfo['address'] = $address;
        } else {
            $dataInfo['address'] = "";
        }
        if(!empty($content)) {
            $dataInfo['content'] = Common::ClearHtml($content,'<p>');
        } else {
            $dataInfo['content'] = "";
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['status'] = $status;
        $dataInfo['sort'] = $sort;
        $dataInfo['user_id'] = $this->user_id;
        return $mettingService->editInfo($dataInfo);
    }
}
