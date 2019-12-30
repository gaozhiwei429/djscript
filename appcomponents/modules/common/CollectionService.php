<?php
/**
 * 产品的收藏信息数据service
 * @文件名称: ZanService
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\CollectionModel;
use appcomponents\modules\passport\PassportService;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class CollectionService extends BaseService
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
     * C端Feeback数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $feedbackModel = new CollectionModel();
        $cityList = $feedbackModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 点赞信息记录入库
     * @param $user_id 提交者用户id
     * @param int $object_id 提交对象的唯一标识
     * @param int $utilization_flag 提交对象标记
     * @return array
     */
    public function commitInfo($user_id, $object_id=0, $utilization_flag=0, $status=1) {
        if(empty($object_id)) {
            return BaseService::returnErrData([], 55700, "请求参数异常");
        }
        $feedbackModel = new CollectionModel();
        $zanParams = [];
        $zanParams[] = ['=', 'user_id', $user_id];
        $zanParams[] = ['=', 'object_id', $object_id];
        $zanParams[] = ['=', 'utilization_flag', $utilization_flag];
        $zanInfoRet = $this->getInfo($zanParams);
        if(BaseService::checkRetIsOk($zanInfoRet)) {
            $zanInfo = BaseService::getRetData($zanInfoRet);
            $id = isset($zanInfo['id']) ? $zanInfo['id'] : 0;
            if(isset($zanInfo['status']) && $zanInfo['status']==$status) {
                $id = isset($zanInfo['id']) ? $zanInfo['id'] : 0;
                return BaseService::returnOkData($id);
            } else {
                $zanInfoData['status'] = $status;
                return $this->editInfo($id, $zanInfoData);
            }
        }
        $userOrganizationService = new UserOrganizationService();
        $passportService = new PassportService();
        //获取当前用户所属的党组织id
        $userOrganizationParams = [];
        $userOrganizationParams[] = ['=', 'user_id', $user_id];
        $userOrganizationParams[] = ['=', 'status', 1];
        $userOrganizationInfoRet = $userOrganizationService->getInfo($userOrganizationParams);
        $userOrganizationInfo = BaseService::getRetData($userOrganizationInfoRet);

        $userInfoParams = [];
        $userInfoParams[] = ['=', 'user_id', $user_id];
        $passportInfoRet = $passportService->getUserInfoByParams($userInfoParams);
        $passportInfo = BaseService::getRetData($passportInfoRet);
        $dataInfo['user_id'] = $user_id;
        $dataInfo['object_id'] = $object_id;
        $dataInfo['utilization_flag'] = $utilization_flag;
        $dataInfo['user_full_name'] = isset($passportInfo['full_name']) ? $passportInfo['full_name'] : "";
        $dataInfo['user_avatar_img'] = isset($passportInfo['avatar_img']) ? $passportInfo['avatar_img'] : "";
        $user_organization_id = isset($userOrganizationInfo['organization_id']) ? $userOrganizationInfo['organization_id'] : 0;
        $user_level_id = isset($userOrganizationInfo['level_id']) ? $userOrganizationInfo['level_id'] : 0;
        $dataInfo['user_organization_id'] = $user_organization_id;
        $dataInfo['user_level_id'] = $user_level_id;
        $organizationService = new OrganizationService();
        $organizationParams = [];
        $organizationParams[] = ['=', 'id', $user_organization_id];
        $organizationInfoRet = $organizationService->getInfo($organizationParams);
        $organizationInfo = BaseService::getRetData($organizationInfoRet);
        $dataInfo['user_organization_title'] = isset($organizationInfo['title']) ? $organizationInfo['title'] : "";
        $levelParams = [];
        $levelParams[] = ['=', 'id', $user_level_id];
        $levelService = new LevelService();
        $levelInfoRet = $levelService->getInfo($levelParams);
        $levelInfo = BaseService::getRetData($levelInfoRet);
        $dataInfo['user_level_title'] = isset($levelInfo['title']) ? $levelInfo['title'] : "";
        $feedback = $feedbackModel->addInfo($dataInfo);
        if($feedback) {
            return BaseService::returnOkData($feedback);
        }
        return BaseService::returnErrData([], 56700, "点赞失败");
    }
    /**
     * 获取点赞详情数据
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        $feedbackModel = new CollectionModel();
        $feedbackInfo = $feedbackModel->getInfoByValue($params);
        if(!empty($feedbackInfo)) {
            return BaseService::returnOkData($feedbackInfo);
        }
        return BaseService::returnErrData([], 511000, "暂无数据");
    }
    /**
     * 获取点赞详情数据
     * @param $params
     * @return array
     */
    public function editInfo($id, $editInfo) {
        $feedbackModel = new CollectionModel();
        $feedbackInfo = $feedbackModel->updateInfo($id, $editInfo);
        if(!empty($feedbackInfo)) {
            return BaseService::returnOkData($feedbackInfo);
        }
        return BaseService::returnErrData([], 511000, "暂无数据");
    }
}
