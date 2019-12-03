<?php
/**
 * 运营平台用户与党组织关系管理表获取service
 * @文件名称: UserOrganizationService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\UserOrganizationModel;
use appcomponents\modules\passport\PassportService;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class UserOrganizationService extends BaseService
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
     * C端Banner数据获取
     * @param $addData
     * @return array
     */
    public function getBannerList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $carModel = new UserOrganizationModel();
        $cityList = $carModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 获取banner详情数据
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        if(empty($params)) {
            return BaseService::returnErrData([], 55000, "请求参数异常");
        }
        $bannerModel = new UserOrganizationModel();
        $bannerInfo = $bannerModel->getInfoByValue($params);
        if(!empty($bannerInfo)) {
            return BaseService::returnOkData($bannerInfo);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 编辑banner详情数据
     * @param $params
     * @return array
     */
    public function editInfo($dataInfo) {
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 56900, "请求参数异常");
        }
        $bannerModel = new UserOrganizationModel();
        $id = isset($dataInfo['id']) ? $dataInfo['id'] : 0;
        $editRest = 0;
        if($id) {
            if(isset($dataInfo['id'])) {
                unset($dataInfo['id']);
            }
            $editRest = $bannerModel->updateInfo($id, $dataInfo);
        } else {
            $editRest = $bannerModel->addInfo($dataInfo);
        }
        if(!empty($editRest)) {
            return BaseService::returnOkData($editRest);
        }
        return BaseService::returnErrData([], 500, "操作异常");
    }
    /**
     * 获取用户账户对应的基本信息数据，为了其他数据表中冗余使用
     * @param $user_id
     * @return array
     */
    public function getUserData($user_id) {
        $passportService = new PassportService();
        $userInfoRet = $passportService->getUserInfoByUserId($user_id);
        if(BaseService::checkRetIsOk($userInfoRet)) {
            $userInfo = BaseService::getRetData($userInfoRet);
            $full_name = isset($userInfo['full_name']) ? $userInfo['full_name'] : "";
            $avatar_img = isset($userInfo['avatar_img']) ? $userInfo['avatar_img'] : "";
            $organization_title = "";
            $organization_id = "";
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
                return BaseService::returnOkData($data);
            } else {
                return BaseService::returnErrData([], 513400, "为了维护党建网络环境，请您实名认证");
            }
        }
        return $userInfoRet;
    }
}
