<?php
/**
 * 用户参加投票相关相关的接口
 * @文件名称: UserVoteController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\OrganizationService;
use appcomponents\modules\common\UserOrganizationService;
use appcomponents\modules\common\UserVoteService;
use appcomponents\modules\common\VoteService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class UserVoteController extends UserBaseController
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
        $vote_id = intval(Yii::$app->request->post('vote_id', 0));
        $newsService = new UserVoteService();
		if(empty($vote_id)) {
			return BaseService::returnErrData([], 54000, "请求参数异常");
		}
        $params = [];
        $params[] = ['=', 'vote_id', $vote_id];
        return $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,[
            'user_id','full_name','avatar_img','vote_id','status'
        ]);
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
        $newsService = new UserVoteService();
        $params = [];
        $params[] = ['=', 'id', $id];
        $params[] = ['!=', 'status', 0];
        return $newsService->getInfo($params,['*']);
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
        $newsService = new UserVoteService();
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
        $newsService = new UserVoteService();
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
    public function actionJoin() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $vote_id = intval(Yii::$app->request->post('vote_id', 0));
        $anwser = Yii::$app->request->post('anwser', []);
        $status = intval(Yii::$app->request->post('status', 0));
        $newsService = new UserVoteService();
        if(empty($vote_id) || empty($anwser)) {
            return BaseService::returnErrData([], 55900, "提交参数异常");
        }
		$dataInfo = [];
        $voteInfo = [];
        if(!empty($vote_id)) {
            $voteParams = [];
            $voteParams[] = ['=', 'id', $vote_id];
            $voteParams[] = ['=', 'status', 1];
            $voteService = new VoteService();
            $voteInfoRet = $voteService->getInfo($voteParams);
            $voteInfo = BaseService::getRetData($voteInfoRet);
            if(!empty($voteInfo)){
                $dataInfo['vote_id'] = isset($voteInfo['id']) ? $voteInfo['id'] : 0;
            } else {
                return BaseService::returnErrData([], 512400, "当前投票还未开始");
            }
        } else {
            return BaseService::returnErrData([], 512400, "当前投票不存在");
        }

        $userOrganizationService = new UserOrganizationService();
        $passportService = new PassportService();
        $userMettingService = new UserVoteService();
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
        $dataInfo['full_name'] = isset($passportInfo['full_name']) ? $passportInfo['full_name'] : "";
        $dataInfo['avatar_img'] = isset($passportInfo['avatar_img']) ? $passportInfo['avatar_img'] : "";
        $dataInfo['start_time'] = isset($voteInfo['start_time']) ? $voteInfo['start_time'] : "";
        $dataInfo['end_time'] = isset($voteInfo['end_time']) ? $voteInfo['end_time'] : "";
        $dataInfo['organization_id'] = isset($voteInfo['organization_id']) ? $voteInfo['organization_id'] : 0;
        $dataInfo['user_organization_id'] = isset($userOrganizationInfo['organization_id']) ? $userOrganizationInfo['organization_id'] : 0;
        $dataInfo['user_level_id'] = isset($userOrganizationInfo['level_id']) ? $userOrganizationInfo['level_id'] : 0;
        if(!empty($anwser) && is_array($anwser)) {
            $dataInfo['anwser'] = json_encode($anwser);
        } else {
            $dataInfo['anwser'] = json_encode([]);
        }
        $userMettingParams[] = ['=', 'user_id', $this->user_id];
        $userMettingParams[] = ['=', 'vote_id', $vote_id];
        $userMettingRet = $userMettingService->getInfo($userMettingParams);
        if(BaseService::checkRetIsOk($userMettingRet)) {
            $userMetting = BaseService::getRetData($userMettingRet);
            $dataInfo['id'] = isset($userMetting['id']) ? $userMetting['id'] : 0;
        } else {
            $dataInfo['id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['status'] = $status;
        return $newsService->editInfo($dataInfo);
    }

    /**
     * 检查当前账号是否参加投票
     * @return array
     */
    public function actionCheckJoin() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $vote_id = intval(Yii::$app->request->post('vote_id', 0));
        $userMettingService = new UserVoteService();
        $params = [];
        if(empty($activity_id)) {
            return BaseService::returnErrData([], 56600, "请求参数异常");
        }
        $mettingService = new VoteService();
        $mettingParams[] = ['=', 'id', $vote_id];
        $mettingInfoRet = $mettingService->getInfo($mettingParams);
        if(!BaseService::checkRetIsOk($mettingInfoRet)) {
            return BaseService::returnErrData([], 57400, "请求参数异常");
        }
        $params[] = ['=', 'activity_id', $vote_id];
        $params[] = ['=', 'user_id', $this->user_id];
        $params[] = ['!=', 'status', 0];
        $userMettingRet = $userMettingService->getInfo($params);
        if(BaseService::checkRetIsOk($userMettingRet)) {
            $userMettingInfo = BaseService::getRetData($userMettingRet);
            return BaseService::returnOkData($userMettingInfo);
        }
        return BaseService::returnErrData([], 517100, "没有参加");
    }
}
