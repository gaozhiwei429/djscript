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
use appcomponents\modules\common\UserOrganizationService;
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
    /**
     * 发表话题
     * @return array
     */
    public function actionSubmit() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $type = intval(Yii::$app->request->post('type', 1));
        $is_anonymous = intval(Yii::$app->request->post('is_anonymous', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $content = trim(Yii::$app->request->post('content', ""));
        $pic_url = Yii::$app->request->post('pic_url', []);
        $longitude_and_latitude = trim(Yii::$app->request->post('longitude_and_latitude', null));
        $address = trim(Yii::$app->request->post('address', null));
        $forumService = new ForumService();
        $data = [];
        if(empty($title)) {
            return BaseService::returnErrData([], 512600, "标题不能为空");
        }
        if(empty($content)) {
            return BaseService::returnErrData([], 512600, "内容不能为空");
        }
        $userOrganizationService = new UserOrganizationService();
        $userOrganizationDataRet = $userOrganizationService->getUserData($this->user_id);
        if(BaseService::checkRetIsOk($userOrganizationDataRet)) {
            $userOrganizationData = BaseService::getRetData($userOrganizationDataRet);
        }
        $data['title'] = $title;
        $data['is_anonymous'] = $is_anonymous;
        $data['type'] = $type;
        $data['content'] = $content;
        if(!empty($pic_url) && is_array($pic_url)) {
            $data['pic_url'] = json_encode($pic_url);
        } else {
            $data['pic_url'] = json_encode([]);
        }
        if(!empty($longitude_and_latitude)) {
            $data['longitude_and_latitude'] = $longitude_and_latitude;
        } else {
            $data['longitude_and_latitude'] = "";
        }
        if(!empty($address)) {
            $data['address'] = $address;
        } else {
            $data['address'] = "";
        }
        if(isset($userOrganizationData['full_name'])) {
            $data['full_name'] = $userOrganizationData['full_name'];
        } else {
            $data['full_name'] = "";
        }
        if(isset($userOrganizationData['avatar_img'])) {
            $data['avatar_img'] = $userOrganizationData['avatar_img'];
        } else {
            $data['avatar_img'] = "";
        }
        if(isset($userOrganizationData['user_id'])) {
            $data['user_id'] = $userOrganizationData['user_id'];
        } else {
            $data['user_id'] = 0;
        }
        if(isset($userOrganizationData['organization_title'])) {
            $data['organization_title'] = $userOrganizationData['organization_title'];
        } else {
            $data['organization_title'] = "";
        }
        if(isset($userOrganizationData['organization_id'])) {
            $data['organization_id'] = $userOrganizationData['organization_id'];
        } else {
            $data['organization_id'] = "";
        }
        return $forumService->editInfo($data);
    }
}
