<?php
/**
 * 考试相关的接口
 * @文件名称: ExamController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\ExamService;
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
        $bannerService = new ExamService();
        $params = [];
        return $bannerService->getList($params, ['id'=>SORT_DESC], $page, $size,
            ['id','uuid','title','organization_uuid','create_time','start_time','overdue_time','examtime','score','passscore','types','decide']
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
        $bannerService = new ExamService();
        $params = [];
        $params[] = ['=', 'id', $id];
        $ret = $bannerService->getInfo($params);
        if(BaseService::checkRetIsOk($ret)) {
            $dataInfo = BaseService::getRetData($ret);
            if(isset($dataInfo['types']) && !empty($dataInfo['types'])) {
                $dataInfo['types'] = json_decode($dataInfo['types'], true);
            }
            return BaseService::returnOkData($dataInfo);
        }
        return $ret;
    }

    /**
     * 试卷数据获取
     * @return array
     */
    public function actionGetQuestion() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        if(empty($id)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $bannerService = new ExamService();
        $params = [];
        $params[] = ['=', 'id', $id];
        $ret = $bannerService->getInfo($params);
        if(BaseService::checkRetIsOk($ret)) {
            $dataInfo = BaseService::getRetData($ret);
            $dataInfo['questionList'] = [];
            $questionIds = [];
            if(isset($dataInfo['types']) && !empty($dataInfo['types'])) {
                $dataInfo['types'] = json_decode($dataInfo['types'], true);
                foreach($dataInfo['types'] as $type=>$typeData) {
                    if(isset($typeData['questions']) && !empty($typeData['questions'])) {
                        $questionsIdArr = explode(',', $typeData['questions']);
                        foreach($questionsIdArr as $questionsId) {
                            $questionIds[] = $questionsId;
                        }
                    }
                }
            }
            if(!empty($questionIds)) {
                $questionService = new QuestionService();
                $questionParams[] = ['in', 'id', $questionIds];
                $questionListRet = $questionService->getList($questionParams,[], 1, count($questionIds),
                    ['id', 'title', 'answer','type','level','problem','analysis'], true
                );
                if(BaseService::checkRetIsOk($questionListRet)) {
                    $questionData = BaseService::getRetData($questionListRet);
                    if(isset($questionData['dataList']) && !empty($questionData['dataList'])) {
                        foreach($questionIds as $k=>$v) {
                            if(isset($questionData['dataList'][$v])) {
                                $dataInfo['questionList'][] = $questionData['dataList'][$v];
                            }
                        }
                    }
                }
            }
            return BaseService::returnOkData($dataInfo);
        }
        return $ret;
    }
}
