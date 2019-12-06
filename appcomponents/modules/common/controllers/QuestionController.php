<?php
/**
 * 考试问题相关的接口
 * @文件名称: QuestionController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\QuestionService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class ExamController extends UserBaseController
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
     * 分页数据获取
     * @return array
     */
    public function actionGetList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $type = intval(Yii::$app->request->post('type', 0));
        $bannerService = new QuestionService();
        $params = [];
        $params[] = ['=', 'status', 1];
        if($type) {
            $params[] = ['=', 'type', $type];
        }
        return $bannerService->getList($params, ['id'=>SORT_DESC], $page, $size);
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
        $bannerService = new QuestionService();
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
        $exam_id = intval(Yii::$app->request->post('exam_id', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $status = intval(Yii::$app->request->post('status', 0));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $analysis = trim(Yii::$app->request->post('analysis', null));
        $level = intval(Yii::$app->request->post('level', 0));
        $type = trim(Yii::$app->request->post('type', 0));
        $problem = trim(Yii::$app->request->post('problem', ""));
        $score = floatval(Yii::$app->request->post('score', 0));
        $passscore = floatval(Yii::$app->request->post('passscore', 0));
        $bannerService = new QuestionService();
        $answer = "";
        if(empty($title)) {
            return BaseService::returnErrData([], 55900, "考题名称不能为空");
        }
        $dataInfo = [];
        if(!empty($type)) {
            $dataInfo['type'] = $type;
            if($type == 2) {
                for($i=0; $i<=8; $i++) {
                    $answerData = Yii::$app->request->post("targs[questionanswer$type][$i]", null);
                    if($answerData) {
                        $answer[] = trim($answerData,'"');
                    }
                }
            } else {
                $answer = Yii::$app->request->post("targs[questionanswer$type]", null);
                $answer = trim($answer,'"');
            }

        } else {
            return BaseService::returnErrData([], 55900, "考题类型不能为空");
        }
        if(empty($answer)) {
            return BaseService::returnErrData([], 55900, "考题答案不能为空");
        }
        //多选的情况下
        if($type == 2) {
            $dataInfo['answer'] = is_array($answer) ? json_encode($answer) : [];
        } else {
            $dataInfo['answer'] = $answer;
        }
        if(!empty($title)) {
            $dataInfo['title'] = $title;
        } else {
            $dataInfo['title'] = "";
        }
        if(!empty($analysis)) {
            $dataInfo['analysis'] = $analysis;
        } else {
            $dataInfo['analysis'] = "";
        }
        if(!empty($level)) {
            $dataInfo['level'] = $level;
        } else {
            $dataInfo['level'] = 1;
        }
        if(!empty($problem)) {
            $dataInfo['problem'] = $problem;
        } else {
            $dataInfo['problem'] = "";
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(!empty($exam_id)) {
            $dataInfo['exam_id'] = $exam_id;
        } else {
            $dataInfo['exam_id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['status'] = $status;
        $dataInfo['sort'] = $sort;
        $dataInfo['score'] = $score;
        $dataInfo['passscore'] = $passscore;
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
        $bannerService = new QuestionService();
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
        $bannerService = new QuestionService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['sort'] = $sort;
        return $bannerService->editInfo($dataInfo);
    }
}
