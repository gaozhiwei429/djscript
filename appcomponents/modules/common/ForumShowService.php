<?php
/**
 * 论坛访问相关的数据获取service
 * @文件名称: ForumShowService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\ForumShowModel;
use appcomponents\modules\passport\PassportService;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class ForumShowService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\common\controllers';
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }
    /**
     * 论坛访问数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $newsModel = new ForumShowModel();
        $cityList = $newsModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 论坛访问详情数据
     * @param $params
     * @return array
     */
    public function editInfo($dataInfo) {
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 56900, "请求参数异常");
        }
        $newsModel = new ForumShowModel();
        $id = isset($dataInfo['id']) ? $dataInfo['id'] : 0;
        $editRest = 0;
        if($id) {
            if(isset($dataInfo['id'])) {
                unset($dataInfo['id']);
            }
            $editRest = $newsModel->updateInfo($id, $dataInfo);
        } else {
            $editRest = $newsModel->addInfo($dataInfo);
        }
        if(!empty($editRest)) {
            return BaseService::returnOkData($editRest);
        }
        return BaseService::returnErrData([], 500, "操作异常");
    }
    /**
     * 论坛访问总数获取
     * @param $params
     * @return array
     */
    public function getCount($params, $fied=['*']) {
        if(empty($params)) {
            return BaseService::returnErrData([], 57800, "请求参数异常");
        }
        $newsModel = new ForumShowModel();
		$count = $newsModel->getCount($params, $fied);
        if(!empty($count)) {
            return BaseService::returnOkData($count);
        }
        return BaseService::returnErrData([], 500, "当前数据不存在");
    }
    /**
     * 添加数据入库
     * @param $data
     * @return array
     */
    public function addInfo($data) {
        if(empty($data)) {
            return BaseService::returnErrData([], 58900, "提交参数异常");
        }
        $forumShowModel = new ForumShowModel();
        $forumAdd = $forumShowModel->addInfo($data);
        if($forumAdd) {
            return BaseService::returnOkData($forumAdd);
        }
        return BaseService::returnErrData([], 59600, "添加数据异常");
    }
    /**
     * 添加访问数据
     * @param $user_id
     */
    public function addData($user_id) {
        if(!empty($user_id)) {
            $organization_title = "未知";
            $organization_id = 0;
            $full_name = "";
            $avatar_img = "";
            $params[] = ['=', 'user_id', $user_id];
            $params[] = ['>=', 'create_time', date("Y-m-d 00:00:00")];
            $params[] = ['<=', 'create_time', date("Y-m-d 23:59:59")];
            $countRet = $this->getCount($params);
            if(!BaseService::checkRetIsOk($countRet)) {
                $passportService = new PassportService();
                $userInfoRet = $passportService->getUserInfoByUserId($user_id);
                if(BaseService::checkRetIsOk($userInfoRet)) {
                    $userInfo = BaseService::getRetData($userInfoRet);
                    $full_name = isset($userInfo['full_name']) ? $userInfo['full_name'] : "";
                    $avatar_img = isset($userInfo['avatar_img']) ? $userInfo['avatar_img'] : "";
                    if(!empty($full_name) && !empty($avatar_img)) {
                        //获取该用户所属党组织
                        $userOrgainzationService = new UserOrganizationService();
                        $userOrgainzationParams[] = ['=', 'user_id', $user_id];
                        $userOrgainzationParams[] = ['=', 'status', 1];
                        $userOrgainzationInfoRet = $userOrgainzationService->getInfo($userOrgainzationParams);
                        if(BaseService::checkRetIsOk($userOrgainzationInfoRet)) {
                            $userOrgainzationInfo = BaseService::getRetData($userOrgainzationInfoRet);
                            $organization_id = isset($userOrgainzationInfo['organization_id']) ? $userOrgainzationInfo['organization_id'] : 0;
                            if($organization_id) {
                                $organizationParams[] = ['=', 'id', $organization_id];
                                $organizationService = new OrganizationService();
                                $organizationInfoRet = $organizationService->getInfo($organizationParams);
                                if(BaseService::checkRetIsOk($organizationInfoRet)) {
                                    $organizationInfo = BaseService::getRetData($organizationInfoRet);
                                    $organization_title = isset($organizationInfo['title']) ? $organizationInfo['title'] : "未知";
                                }
                            }
                        }
                        $data['user_id'] = $user_id;
                        $data['organization_title'] = $organization_title;
                        $data['organization_id'] = $organization_id;
                        $data['full_name'] = $full_name;
                        $data['avatar_img'] = $avatar_img;
                        return $this->addInfo($data);
                    } else {
                        return BaseService::returnErrData([], 513400, "为了维护党建网络环境，请您实名认证");
                    }
                }
                return BaseService::returnErrData([], 513400, "当前账号不可用");
            }
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData([], 514000, "请求参数有误");
    }
}
