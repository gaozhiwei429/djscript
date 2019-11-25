<?php
/**
 * 党史上的今天相关的接口
 * @文件名称: DangHistoryController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\DangHistoryService;
use appcomponents\modules\common\DangTodayService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class DangHistoryController extends UserBaseController
{
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 获取党史的今日数据
     * @return array
     */
    public function actionGetHistory() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $dangTodayService = new DangTodayService();
        $month = intval(date("m"));
        $day = intval(date("d"));
        $params[] = ['!=', 'status', 0];
        $params[] = ['<=', 'month_and_day', $month.".".$day];
        return $dangTodayService->getList($params, ['month_and_day'=>SORT_DESC,'id'=>SORT_ASC], $page, $size);
    }
    /**
     * 获取某一天的党史的今日分页数据获取
     * @return array
     */
    public function actionGetList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $dang_today_id = intval(Yii::$app->request->post('dang_today_id', 0));
        if(empty($dang_today_id)) {
            return BaseService::returnErrData([], 500, "请求参数异常");
        }
        $bannerService = new DangHistoryService();
        $params = [];
        $month = intval(date("m"));
        $day = intval(date("d"));
        $params[] = ['!=', 'status', 0];
        $params[] = ['=', 'month', $month];
        $params[] = ['=', 'day', $day];
        $params[] = ['=', 'dang_today_id', $dang_today_id];
        return $bannerService->getList($params, ['sort'=>SORT_DESC,'id'=>SORT_ASC], 1, -1);
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
        $bannerService = new DangHistoryService();
        $params = [];
        $params[] = ['=', 'id', $id];
        return $bannerService->getInfo($params);
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
        $month = intval(Yii::$app->request->post('month', ""));
        $day = intval(Yii::$app->request->post('day', ""));
        $status = intval(Yii::$app->request->post('status', 0));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $content = trim(Yii::$app->request->post('content', ""));
        $bannerService = new DangHistoryService();
        $dataInfo = [];
        if(empty($title)) {
            return BaseService::returnErrData([], 55900, "标题不能为空");
        }
        if(empty($month)) {
            return BaseService::returnErrData([], 55900, "所属月份不能为空");
        }
        if(empty($day)) {
            return BaseService::returnErrData([], 55900, "所属天不能为空");
        }
        if(empty($content)) {
            return BaseService::returnErrData([], 55900, "描述内容不能为空");
        }
        if(!empty($title)) {
            $dataInfo['title'] = $title;
        } else {
            $dataInfo['title'] = "";
        }
        if(!empty($month)) {
            $dataInfo['month'] = $month;
        } else {
            $dataInfo['month'] = "";
        }
        if(!empty($day)) {
            $dataInfo['day'] = $day;
        } else {
            $dataInfo['day'] = "";
        }
        if(!empty($content)) {
            $dataInfo['content'] = $content;
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
        return $bannerService->editInfo($dataInfo);
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
        $bannerService = new DangHistoryService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['status'] = $status;
        return $bannerService->editInfo($dataInfo);
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
        $bannerService = new DangHistoryService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['sort'] = $sort;
        return $bannerService->editInfo($dataInfo);
    }
}
