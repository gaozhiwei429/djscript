<?php
/**
 * 论坛相关相关的接口
 * @文件名称: ForumController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\ForumService;
use appcomponents\modules\common\ForumShowService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class ForumController extends UserBaseController
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
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $forumShowService = new ForumShowService();
        $forumShowService->addData($this->user_id);
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $type = intval(Yii::$app->request->post('type', 0));
        $search_type = intval(Yii::$app->request->post('search_type', 0));//1为标题，2位发布人
        $title = trim(Yii::$app->request->post('title', ""));
        $is_hot = intval(Yii::$app->request->post('is_hot', 0));
        $newsService = new ForumService();
        $params = [];
        $params[] = ['!=', 'status', 0];
		if(!empty($type)) {
			$params[] = ['=', 'type', $type];
		}
        if($search_type==1) {
            if(!empty($title)) {
                $params[] = ['like', 'title', $title];
            }
        }
        if($search_type==2) {
            if(!empty($title)) {
                $passportService = new PassportService();
                $passportParams[] = ['=', 'full_name', $title];
                $passportRet = $passportService->getUserInfoByParams($passportParams);
                if(BaseService::checkRetIsOk($passportRet)) {
                    $passportInfo = BaseService::getRetData($passportRet);
                    if(!empty($passportInfo)) {
                        $params[] = ['=', 'user_id', $passportInfo['user_id']];
                    }
                } else {
                    $data = [
                        'dataList' => [],
                        'count' => 0,
                    ];
                    return BaseService::returnOkData($data);
                }
            }
        }
        $params[] = ['=', 'is_hot', $is_hot];
        return $newsService->getList($params, ['sort'=>SORT_DESC,'id'=>SORT_ASC], $page, $size,['*']);
    }

    /**
     * 详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $forumShowService = new ForumShowService();
        $forumShowService->addData($this->user_id);
        $id = intval(Yii::$app->request->post('id', 0));
        if(empty($id)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $newsService = new ForumService();
        $params = [];
        $params[] = ['=', 'id', $id];
        $params[] = ['!=', 'status', 0];
        return $newsService->getInfo($params,['*']);
    }
    /**
     * 获取今日访问列表数据
     * @return array
     */
    public function actionGetShowList() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $forumShowService = new ForumShowService();
        $params[] = [];
        $params[] = ['>=', 'create_time', date("Y-m-d 00:00:00")];
        $params[] = ['<=', 'create_time', date("Y-m-d 23:59:59")];
        return $forumShowService->getList($params, ['id'=>SORT_ASC], $page, $size,['*']);
    }
}
