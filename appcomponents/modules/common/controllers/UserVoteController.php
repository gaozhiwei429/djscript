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
use appcomponents\modules\common\UserVoteService;
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
        $newsService = new UserVoteService();
        $params = [];
//        $params[] = ['!=', 'status', 0];
        return $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,['*']);
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
    public function actionEdit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $vote_id = intval(Yii::$app->request->post('vote_id', 0));
        $anwser = intval(Yii::$app->request->post('anwser', 0));
        $organization_id = intval(Yii::$app->request->post('organization_id', 0));
        $newsService = new UserVoteService();
        if(empty($vote_id) || empty($anwser)) {
            return BaseService::returnErrData([], 55900, "提交参数异常");
        }
		$dataInfo = [];
        if(!empty($vote_id)) {
            $dataInfo['vote_id'] = $vote_id;
        } else {
            $dataInfo['vote_id'] = 0;
        }
        if(!empty($anwser)) {
            $dataInfo['anwser'] = $anwser;
        } else {
            $dataInfo['anwser'] = 0;
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(!empty($organization_id)) {
            $dataInfo['organization_id'] = $organization_id;
        } else {
            $dataInfo['organization_id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        return $newsService->editInfo($dataInfo);
    }
}
