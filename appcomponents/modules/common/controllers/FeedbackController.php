<?php
/**
 * 评论相关相关的接口
 * @文件名称: FeedbackController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\FeedbackService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class FeedbackController extends UserBaseController
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
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        $newsService = new FeedbackService();
        $params = [];
        $params[] = ['=', 'utilization_flag', $utilization_flag];
        $params[] = ['=', 'object_id', $object_id];
        $params[] = ['!=', 'status', 0];
        return $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,['*']);
    }
    /**
     * 提交评论数据
     * @return array
     */
    public function actionSubmit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $utilization_flag = intval(Yii::$app->request->post('utilization_flag', 0));
        $object_id = intval(Yii::$app->request->post('object_id', 0));
        $content = trim(Yii::$app->request->post('content', ""));
        $pic_url = Yii::$app->request->post('pic_url', []);
        if(!$utilization_flag || !$object_id) {
            return BaseService::returnErrData([], 53800, "请求参数异常");
        }
        if(!empty($pic_url) && !is_array($pic_url)) {
            return BaseService::returnErrData([], 57500, "图片格式提交不合法");
        }
        $feebackService = new FeedbackService();
        return $feebackService->addData($this->user_id, $content, $object_id, $utilization_flag, $pic_url);
    }
}
