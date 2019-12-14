<?php
/**
 * 党史人物相关的接口
 * @文件名称: LeadersController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\LeadersService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class LeadersController extends UserBaseController
{
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 分页数据获取
     * @return array
     */
    public function actionGetList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $bannerService = new LeadersService();
        $params = [];
        return $bannerService->getList($params, ['sort'=>SORT_DESC,'id'=>SORT_ASC], $page, $size,
            ['id','full_name','avatar_img','sort','life_start_date','life_end_date']
        );
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
        $bannerService = new LeadersService();
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
        $full_name = trim(Yii::$app->request->post('full_name', ""));
        $avatar_img = trim(Yii::$app->request->post('avatar_img', ""));
        $status = intval(Yii::$app->request->post('status', 0));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $lifedate = trim(Yii::$app->request->post('lifedate', ""));
        $content = trim(Yii::$app->request->post('content', ""));
        $introduction = trim(Yii::$app->request->post('introduction', ""));
        $bannerService = new LeadersService();
        $dataInfo = [];
        if(empty($full_name)) {
            return BaseService::returnErrData([], 55900, "姓名不能为空");
        }
        if(empty($avatar_img)) {
            return BaseService::returnErrData([], 55900, "请上传人物头像");
        }
        if(empty($content)) {
            return BaseService::returnErrData([], 55900, "描述内容不能为空");
        }
        if(empty($introduction)) {
            return BaseService::returnErrData([], 55900, "简介内容不能为空");
        }
        if(empty($lifedate)) {
            return BaseService::returnErrData([], 55900, "生世周期不能为空");
        } else {
            $lifedateArr = explode(' - ', $lifedate);
            if(isset($lifedateArr[0]) && !empty($lifedateArr[0])) {
                $dataInfo['life_start_date'] = date("Y-m-d", strtotime(trim($lifedateArr[0])));
            }
            if(isset($lifedateArr[1]) && !empty($lifedateArr[1])) {
                $dataInfo['life_end_date'] = date("Y-m-d", strtotime(trim($lifedateArr[1])));
            }
        }
        if(empty($dataInfo['life_start_date']) || empty($dataInfo['life_end_date'])) {
            return BaseService::returnErrData([], 55900, "生世周期数据不完善");
        }
        if(!empty($full_name)) {
            $dataInfo['full_name'] = $full_name;
        } else {
            $dataInfo['full_name'] = "";
        }
        if(!empty($avatar_img)) {
            $dataInfo['avatar_img'] = $avatar_img;
        } else {
            $dataInfo['avatar_img'] = "";
        }
        if(!empty($content)) {
            $dataInfo['content'] = $content;
        } else {
            $dataInfo['content'] = "";
        }
        if(!empty($introduction)) {
            $dataInfo['introduction'] = $introduction;
        } else {
            $dataInfo['introduction'] = "";
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
//        var_dump($dataInfo);die;
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
        $bannerService = new LeadersService();
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
        $bannerService = new LeadersService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['sort'] = $sort;
        return $bannerService->editInfo($dataInfo);
    }
}
