<?php
/**
 * 党组织关系转移申请接口
 * @文件名称: OrganizationApplyController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\OrganizationApplyService;
use appcomponents\modules\common\OrganizationService;
use appcomponents\modules\common\UserOrganizationService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class OrganizationApplyController extends  UserBaseController
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
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $apply_user_id = intval(Yii::$app->request->post('apply_user_id',  0));
        $send_user_id = intval(Yii::$app->request->post('send_user_id',  0));
        $organizationApplyService = new OrganizationApplyService();
        $params = [];
        if($apply_user_id) {
            $params[] = ['=', 'apply_user_id', $apply_user_id];
        } else if($send_user_id) {
            $params[] = ['=', 'send_user_id', $send_user_id];
        } else {
            $params[] = ['=', 'submit_user_id', $this->user_id];
        }

        $organizationApplyListRet = $organizationApplyService->getList($params, ['id'=>SORT_DESC], $page, $size,
            ['old_organization_id','new_organization_id','user_id','submit_user_id','apply_user_id','send_user_id','type','area_type','status','user_id']
        );
        if(BaseService::checkRetIsOk($organizationApplyListRet)) {
            $organizationApplyList = BaseService::getRetData($organizationApplyListRet);
//            $organizationApplyData = isset($organizationApplyList['dataList']) ? $organizationApplyList['dataList'] : [];
            if(isset($organizationApplyList['dataList']) && !empty($organizationApplyList['dataList'])) {
                $organizationIds = [];
                $userIds = [];
                foreach($organizationApplyList['dataList'] as $organizationApplyInfo) {
                    if(isset($organizationApplyInfo['old_organization_id']) && $organizationApplyInfo['old_organization_id']) {
                        $organizationIds[] = $organizationApplyInfo['old_organization_id'];
                    }
                    if(isset($organizationApplyInfo['new_organization_id']) && $organizationApplyInfo['new_organization_id']) {
                        $organizationIds[] = $organizationApplyInfo['new_organization_id'];
                    }
                    if(isset($organizationApplyInfo['user_id']) && $organizationApplyInfo['user_id']) {
                        $userIds[] = $organizationApplyInfo['user_id'];
                    }
                    if(isset($organizationApplyInfo['submit_user_id']) && $organizationApplyInfo['submit_user_id']) {
                        $userIds[] = $organizationApplyInfo['submit_user_id'];
                    }
                    if(isset($organizationApplyInfo['apply_user_id']) && $organizationApplyInfo['apply_user_id']) {
                        $userIds[] = $organizationApplyInfo['apply_user_id'];
                    }
                    if(isset($organizationApplyInfo['send_user_id']) && $organizationApplyInfo['send_user_id']) {
                        $userIds[] = $organizationApplyInfo['send_user_id'];
                    }
                }
                $organizationList = [];
                if(!empty($organizationIds)) {
                    $organizationIds = array_unique($organizationIds);
                    $organizationParams[] = ['in','id', $organizationIds];
                    $organizationService = new OrganizationService();
                    $organizationListRet = $organizationService->getDataListByIndexId($organizationParams, [], 1, count($organizationIds), ['id','title'],true);
                    if(BaseService::checkRetIsOk($organizationListRet)) {
                        $organizationListData = BaseService::getRetData($organizationListRet);
                        $organizationList = isset($organizationListData['dataList']) ? $organizationListData['dataList'] : [];
                    }
                }
                $passportList = [];
                if(!empty($userIds)) {
                    $userIds = array_unique($userIds);
                    $userParams[] = ['in','id', $userIds];
                    $passportService = new PassportService();
                    $passportListRet = $passportService->getList($userParams, [], 1, count($userIds), ['id'],true,true);
                    if(BaseService::checkRetIsOk($passportListRet)) {
                        $passportListData = BaseService::getRetData($passportListRet);
                        $passportList = isset($passportListData['dataList']) ? $passportListData['dataList'] : [];
                    }
                }
                foreach($organizationApplyList['dataList'] as &$organizationApplyInfo) {
                    $organizationApplyInfo['old_organization_title'] = "";
                    $organizationApplyInfo['new_organization_title'] = "";
                    $organizationApplyInfo['full_name'] = "";
                    $organizationApplyInfo['avatar_img'] = "";
                    $organizationApplyInfo['submit_user_full_name'] = "";
                    $organizationApplyInfo['submit_user_avatar_img'] = "";
                    $organizationApplyInfo['apply_user_full_name'] = "";
                    $organizationApplyInfo['apply_user_avatar_img'] = "";
                    $organizationApplyInfo['send_user_full_name'] = "";
                    $organizationApplyInfo['send_user_avatar_img'] = "";
                    $organizationApplyInfo['apply_user_avatar_img'] = "";
                    if(isset($organizationApplyInfo['old_organization_id']) && $organizationList[$organizationApplyInfo['old_organization_id']]) {
                        $organizationApplyInfo['old_organization_title'] = isset($organizationList[$organizationApplyInfo['old_organization_id']]['title']) ?
                            $organizationList[$organizationApplyInfo['old_organization_id']]['title'] : "";
                    }
                    if(isset($organizationApplyInfo['new_organization_id']) && $organizationList[$organizationApplyInfo['new_organization_id']]) {
                        $organizationApplyInfo['new_organization_title'] = isset($organizationList[$organizationApplyInfo['new_organization_id']]['title']) ?
                            $organizationList[$organizationApplyInfo['new_organization_id']]['title'] : "";
                    }
                    if(isset($organizationApplyInfo['user_id']) && isset($passportList[$organizationApplyInfo['user_id']])) {
                        $organizationApplyInfo['full_name'] = isset($passportList[$organizationApplyInfo['user_id']]['full_name']) ?
                            $passportList[$organizationApplyInfo['user_id']]['full_name'] : "";
                        $organizationApplyInfo['avatar_img'] = isset($passportList[$organizationApplyInfo['user_id']]['avatar_img']) ?
                            $passportList[$organizationApplyInfo['user_id']]['avatar_img'] : "";
                    }
                    if(isset($organizationApplyInfo['submit_user_id']) && isset($passportList[$organizationApplyInfo['submit_user_id']])) {
                        $organizationApplyInfo['submit_user_full_name'] = isset($passportList[$organizationApplyInfo['submit_user_id']]['full_name']) ?
                            $passportList[$organizationApplyInfo['submit_user_id']]['full_name'] : "";
                        $organizationApplyInfo['submit_user_avatar_img'] = isset($passportList[$organizationApplyInfo['submit_user_id']]['avatar_img']) ?
                            $passportList[$organizationApplyInfo['submit_user_id']]['avatar_img'] : "";
                    }
                    if(isset($organizationApplyInfo['apply_user_id']) && isset($passportList[$organizationApplyInfo['apply_user_id']])) {
                        $organizationApplyInfo['apply_user_full_name'] = isset($passportList[$organizationApplyInfo['apply_user_id']]['full_name']) ?
                            $passportList[$organizationApplyInfo['apply_user_id']]['full_name'] : "";
                        $organizationApplyInfo['apply_user_avatar_img'] = isset($passportList[$organizationApplyInfo['apply_user_id']]['avatar_img']) ?
                            $passportList[$organizationApplyInfo['apply_user_id']]['avatar_img'] : "";
                    }
                    if(isset($organizationApplyInfo['send_user_id']) && isset($passportList[$organizationApplyInfo['send_user_id']])) {
                        $organizationApplyInfo['send_user_full_name'] = isset($passportList[$organizationApplyInfo['send_user_id']]['full_name']) ?
                            $passportList[$organizationApplyInfo['send_user_id']]['full_name'] : "";
                        $organizationApplyInfo['send_user_avatar_img'] = isset($passportList[$organizationApplyInfo['send_user_id']]['avatar_img']) ?
                            $passportList[$organizationApplyInfo['send_user_id']]['avatar_img'] : "";
                    }
                }
            }
            return BaseService::returnOkData($organizationApplyList);
        }
        return $organizationApplyListRet;
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
        if(empty($id)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $organizationService = new OrganizationApplyService();
        $params = [];
        $params[] = ['=', 'id', $id];
        return $organizationService->getInfo($params);
    }
    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetStatus() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $uuid = trim(Yii::$app->request->post('uuid', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $organizationService = new OrganizationApplyService();
        if(empty($uuid)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['uuid'] = $uuid;
        $dataInfo['status'] = $status;
        return $organizationService->editInfo($dataInfo);
    }
    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetSort() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $uuid = trim(Yii::$app->request->post('uuid', 0));
        $sort = intval(Yii::$app->request->post('sort',  0));
        $organizationService = new OrganizationApplyService();
        if(empty($uuid)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['uuid'] = $uuid;
        $dataInfo['sort'] = $sort;
        return $organizationService->editInfo($dataInfo);
    }
    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSubmit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $area_type = intval(Yii::$app->request->post('area_type', 0));
        $type = intval(Yii::$app->request->post('type',  0));
        $old_organization_id = intval(Yii::$app->request->post('old_organization_id',  0));
        $new_organization_id = intval(Yii::$app->request->post('new_organization_id',  0));
        $sex = intval(Yii::$app->request->post('sex',  0));
        $age = intval(Yii::$app->request->post('age',  0));
        $nation = trim(Yii::$app->request->post('nation',  ""));
        $user_status = intval(Yii::$app->request->post('user_status',  0));
        $mobile = trim(Yii::$app->request->post('mobile', ""));
        $paid_up_date = Yii::$app->request->post('paid_up_date', "");
        if(is_array($paid_up_date)) {
            return BaseService::returnErrData([], 522900, "党费缴纳至月份提交数据不合法");
        }
        $paid_up_date = trim(Yii::$app->request->post('paid_up_date', ""));
        $apply_user_id = intval(Yii::$app->request->post('apply_user_id',  0));
        $send_user_id = intval(Yii::$app->request->post('send_user_id',  0));
        $overdue_day = intval(Yii::$app->request->post('overdue_day',  0));
        $old_organization_address = trim(Yii::$app->request->post('old_organization_address',  ""));
        $old_organization_mobile = trim(Yii::$app->request->post('old_organization_mobile',  ""));
        $old_organization_fax = trim(Yii::$app->request->post('old_organization_fax',  ""));
        $old_organization_post_code = trim(Yii::$app->request->post('old_organization_post_code',  ""));
        $recommendation = Yii::$app->request->post('recommendation',  []);
        $organizationApplyService = new OrganizationApplyService();
        $organizationService = new OrganizationService();
        $userOrganizationService = new UserOrganizationService();
        $dataInfo = [];
        if(empty($area_type)) {
            return BaseService::returnErrData([], 513300, "请选择区域");
        }
        $dataInfo['area_type'] = $area_type;
        if(empty($type)) {
            return BaseService::returnErrData([], 513700, "请选择转移类型");
        }
        $dataInfo['type'] = $type;
        if(empty($old_organization_id)) {
            return BaseService::returnErrData([], 514100, "请选择原党组织");
        } else {
            $organizationParams = [];
            $organizationParams[] = ['=', 'id', $new_organization_id];
            $organizationParams[] = ['!=', 'status', -1];
            $userOrganizationInfoRet = $organizationService->getInfo($organizationParams);
            if(!BaseService::checkRetIsOk($userOrganizationInfoRet)) {
                return BaseService::returnErrData([], 514800, "原党组织不存在");
            }
        }
        $dataInfo['old_organization_id'] = $old_organization_id;
        if(empty($new_organization_id)) {
            return BaseService::returnErrData([], 515300, "请选择目标党组织");
        } else {
            $organizationParams = [];
            $organizationParams[] = ['=', 'id', $new_organization_id];
            $organizationParams[] = ['!=', 'status', -1];
            $userOrganizationInfoRet = $organizationService->getInfo($organizationParams);
            if(!BaseService::checkRetIsOk($userOrganizationInfoRet)) {
                return BaseService::returnErrData([], 516000, "目标党组织不存在");
            }
        }
        $dataInfo['new_organization_id'] = $new_organization_id;
        if(empty($sex)) {
            return BaseService::returnErrData([], 516500, "请选择性别");
        }
        $dataInfo['sex'] = $sex;
        if(empty($age)) {
            return BaseService::returnErrData([], 516900, "请输入年龄");
        }
        $dataInfo['age'] = $age;
        if(empty($nation)) {
            return BaseService::returnErrData([], 517400, "请选择民族");
        }
        $dataInfo['nation'] = $nation;
        if(empty($user_status)) {
            return BaseService::returnErrData([], 517700, "请选择党员状态");
        }
        $dataInfo['user_status'] = $user_status;
        if(empty($paid_up_date)) {
            return BaseService::returnErrData([], 518100, "请输入党费缴纳至月份");
        }
        $dataInfo['paid_up_date'] = date("Y-m-d", strtotime($paid_up_date));
        if(empty($mobile)) {
            return BaseService::returnErrData([], 518500, "请输入联系电话");
        }
        $dataInfo['mobile'] = $mobile;
        if(empty($apply_user_id)) {
            return BaseService::returnErrData([], 518900, "请选择审批人");
        } else {
            $userOrganizationParams = [];
            $userOrganizationParams[] = ['=', 'user_id', $apply_user_id];
            $userOrganizationParams[] = ['=', 'status', 1];
            $userOrganizationInfoRet = $userOrganizationService->getInfo($userOrganizationParams);
            if(!BaseService::checkRetIsOk($userOrganizationInfoRet)) {
                return BaseService::returnErrData([], 519600, "审批人不存在");
            }
        }
        $dataInfo['apply_user_id'] = $apply_user_id;
        if(empty($send_user_id)) {
            return BaseService::returnErrData([], 520100, "请选择抄送人");
        } else {
            $userOrganizationParams = [];
            $userOrganizationParams[] = ['=', 'user_id', $send_user_id];
            $userOrganizationParams[] = ['=', 'status', 1];
            $userOrganizationInfoRet = $userOrganizationService->getInfo($userOrganizationParams);
            if(!BaseService::checkRetIsOk($userOrganizationInfoRet)) {
                return BaseService::returnErrData([], 520800, "抄送人不存在");
            }
        }
        $dataInfo['send_user_id'] = $send_user_id;
        if(empty($old_organization_address)) {
            return BaseService::returnErrData([], 521300, "请输入原所在基层党委通讯地址");
        }
        $dataInfo['old_organization_address'] = $old_organization_address;
        if(empty($overdue_day)) {
            return BaseService::returnErrData([], 521700, "请输入有效期（天）");
        }
        $dataInfo['overdue_time'] = date("Y-m-d",strtotime("+$overdue_day day"));
        if(empty($old_organization_mobile)) {
            return BaseService::returnErrData([], 522100, "请输入原所在基层党委联系电话");
        }
        $dataInfo['old_organization_mobile'] = $old_organization_mobile;
        $dataInfo['old_organization_fax'] = $old_organization_fax;
        $dataInfo['old_organization_post_code'] = $old_organization_post_code;
        //介绍信
        if(!is_array($recommendation)) {
            return BaseService::returnErrData([], 522800, "介绍信数据不合法");
        }
        $dataInfo['recommendation'] = $recommendation;
        $dataInfo['user_id'] = $this->user_id;
        return $organizationApplyService->editInfo($dataInfo);
    }
}
