<?php
/**
 * 用户参加会议相关相关的接口
 * @文件名称: UserMettingController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\MettingService;
use appcomponents\modules\common\UserMettingService;
use appcomponents\modules\common\UserOrganizationService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class UserMettingController extends UserBaseController
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
        $metting_id = intval(Yii::$app->request->post('metting_id', 0));
        $newsService = new UserMettingService();
        $params = [];
        if(empty($metting_id)) {
            return BaseService::returnErrData([], 53900, "请求参数异常");
        }
		if(!empty($metting_id)) {
			$params[] = ['=', 'metting_id', $metting_id];
		}
        return $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,
            [
                'user_id','full_name','avatar_img','metting_id','status'
            ]
        );
    }
    /**
     * 列表数据获取
     * @return array
     */
    public function actionJoin() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $metting_id = intval(Yii::$app->request->post('metting_id', 0));
        $status = intval(Yii::$app->request->post('status', 1));
        $userMettingService = new UserMettingService();
        $params = [];
        if(empty($metting_id)) {
            return BaseService::returnErrData([], 56600, "请求参数异常");
        }
        $mettingService = new MettingService();
        $mettingParams[] = ['=', 'id', $metting_id];
        $mettingInfoRet = $mettingService->getInfo($mettingParams);
        $mettingInfo = [];
        if(!BaseService::checkRetIsOk($mettingInfoRet)) {
            return BaseService::returnErrData([], 57400, "请求参数异常");
        }
        $mettingInfo = BaseService::getRetData($mettingInfoRet);
        $params[] = ['=', 'metting_id', $metting_id];
        $params[] = ['=', 'user_id', $this->user_id];
        $userMettingRet = $userMettingService->getInfo($params);
        if(BaseService::checkRetIsOk($userMettingRet)) {
            $userMettingInfo = BaseService::getRetData($userMettingRet);
            $userMettingInfo['status'] = $status;
            $userMettingService->editInfo($userMettingInfo);
        } else {
            //查看会议记录是否存在，如果不存在那么
            $userMettingParams = [];
            $userMettingParams[] = ['=', 'user_id', $this->user_id];
            $userMettingParams[] = ['=', 'metting_id', $metting_id];
            $userMettingInfoRet = $userMettingService->getInfo($userMettingParams);
            $userMettingInfo = BaseService::getRetData($userMettingInfoRet);
            if(empty($userMettingInfo)) {
                $userOrganizationService = new UserOrganizationService();
                $passportService = new PassportService();
                //获取当前用户所属的党组织id
                $userOrganizationParams = [];
                $userOrganizationParams[] = ['=', 'user_id', $this->user_id];
                $userOrganizationParams[] = ['=', 'status', 1];
                $userOrganizationInfoRet = $userOrganizationService->getInfo($userOrganizationParams);
                $userOrganizationInfo = BaseService::getRetData($userOrganizationInfoRet);
                $userInfoParams = [];
                $userInfoParams[] = ['=', 'user_id', $this->user_id];
                $passportInfoRet = $passportService->getUserInfoByParams($userInfoParams);
                $passportInfo = BaseService::getRetData($passportInfoRet);
                $addData = [
                    'user_id' => $this->user_id,
                    'full_name' => isset($passportInfo['full_name']) ? $passportInfo['full_name'] : "",
                    'avatar_img' => isset($passportInfo['avatar_img']) ? $passportInfo['avatar_img'] : "",
                    'start_time' => isset($data['start_time']) ? $data['start_time'] : "",
                    'end_time' => isset($data['end_time']) ? $data['end_time'] : "",
                    'organization_id' => isset($data['organization_id']) ? $data['organization_id'] : 0,
                    'metting_id' => $metting_id,
                    'status' => $status,
                    'user_organization_id' => isset($userOrganizationInfo['organization_id']) ? $userOrganizationInfo['organization_id'] : 0,
                    'user_level_id' => isset($userOrganizationInfo['level_id']) ? $userOrganizationInfo['level_id'] : 0,
                ];
                $userMettingService->editInfo($addData);
            }
        }
        //1待参加，2参加，3缺席，4请假，5迟到
        if($status==2) {
            $data = [
                'id' =>$metting_id,
                'join_people_num' =>isset($mettingInfo['join_people_num']) ? $mettingInfo['join_people_num']+1 : 0,
            ];
            $mettingService->editInfo($data);
        } else if($status==4){
            $data = [
                'id' =>$metting_id,
                'leave_people_num' =>isset($mettingInfo['leave_people_num']) ? $mettingInfo['leave_people_num']+1 : 0,
            ];
            $mettingService->editInfo($data);
        } else if($status==5){
            $data = [
                'id' =>$metting_id,
                'late_people_num' =>isset($mettingInfo['late_people_num']) ? $mettingInfo['late_people_num']+1 : 0,
            ];
            $mettingService->editInfo($data);
        } else{
        }
        return BaseService::returnOkData([]);
    }
}
