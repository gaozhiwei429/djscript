<?php
/**
 * 运营平台党员相关的管理表获取service
 * @文件名称: DangyuanService.php
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
class DangyuanService extends BaseService
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
     * 数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $carModel = new UserOrganizationModel();
        $dataList = $carModel->getListData($params, $orderBy, $offset, $limit, $fied);
        $organizationIds = [];
        $userIds = [];
        $levelIds = [];
        $organizationData = [];
        $passportData = [];
        if(!empty($dataList)) {
            if(isset($dataList['dataList']) && !empty($dataList['dataList'])) {
                foreach($dataList['dataList'] as $k=>$dataInfo) {
                    if(isset($dataInfo['organization_id'])) {
                        $organizationIds[] = $dataInfo['organization_id'];
                    }
                    if(isset($dataInfo['user_id'])) {
                        $userIds[] = $dataInfo['user_id'];
                    }
                    if(isset($dataInfo['level_id'])) {
                        $levelIds[] = $dataInfo['level_id'];
                    }
                }
                if(!empty($organizationIds)) {
                    $organizationParams = [];
                    $organizationParams[] = ['in', 'id', $organizationIds];
                    $organizationService = new OrganizationService();
                    $organizationRest = $organizationService->getDataListByIndexId($organizationParams, [], 1, -1, ['*'], true);
                    if(BaseService::checkRetIsOk($organizationRest)) {
                        $organizationDataList = BaseService::getRetData($organizationRest);
                        $organizationData = isset($organizationDataList['dataList']) ? $organizationDataList['dataList'] : [];
                    }
                }
                if(!empty($userIds)) {
                    $passportParams[] = ['in', 'id', $userIds];
                    $passportService = new PassportService();
                    $passportRest = $passportService->getList($passportParams, [], 1, -1,
                        ['*']
                        , true, true);
                    if(BaseService::checkRetIsOk($passportRest)) {
                        $passportDataList = BaseService::getRetData($passportRest);
                        $passportData = isset($passportDataList['dataList']) ? $passportDataList['dataList'] : [];
                    }
                }
                if(!empty($levelIds)) {
                    $levelParams[] = ['in', 'id', $levelIds];
                    $levelService = new LevelService();
                    $levelRest = $levelService->getList($levelParams, [], 1, -1, ['*'], true);
                    if(BaseService::checkRetIsOk($levelRest)) {
                        $levelDataList = BaseService::getRetData($levelRest);
                        $levelData = isset($levelDataList['dataList']) ? $levelDataList['dataList'] : [];
                    }
                }
                foreach($dataList['dataList'] as $k=>&$dataInfo) {
                    $dataInfo['username'] = "";
                    $dataInfo['nickname'] = "";
                    $dataInfo['full_name'] = "";
                    $dataInfo['avatar_img'] = "";
                    $dataInfo['organization_title'] = "";
                    $dataInfo['level_title'] = "";
                    if(isset($dataInfo['user_id']) && isset($passportData[$dataInfo['user_id']])) {
                        $dataInfo['username'] = isset($passportData[$dataInfo['user_id']]['username']) ? $passportData[$dataInfo['user_id']]['username'] : "";
                        $dataInfo['nickname'] = isset($passportData[$dataInfo['user_id']]['nickname']) ? $passportData[$dataInfo['user_id']]['nickname'] : "";
                        $dataInfo['full_name'] = isset($passportData[$dataInfo['user_id']]['full_name']) ? $passportData[$dataInfo['user_id']]['full_name'] : "";
                        $dataInfo['avatar_img'] = isset($passportData[$dataInfo['user_id']]['avatar_img']) ? $passportData[$dataInfo['user_id']]['avatar_img'] : "";
                    }
                    if(isset($dataInfo['organization_id']) && isset($organizationData[$dataInfo['organization_id']])) {
                        $dataInfo['organization_title'] = isset($organizationData[$dataInfo['organization_id']]['title']) ? $organizationData[$dataInfo['organization_id']]['title'] : "";
                    }
                    if(isset($dataInfo['level_id']) && isset($levelData[$dataInfo['level_id']])) {
                        $dataInfo['level_title'] = isset($levelData[$dataInfo['level_id']]['title']) ? $levelData[$dataInfo['level_id']]['title'] : "";
                    }
                }
            }
            return BaseService::returnOkData($dataList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 获取党员基本信息数据
     * @param array $params
     */
    public function getInfo($params) {
        if(empty($params)) {
            return BaseService::returnErrData([], 512000, "请求参数异常");
        }
        $carModel = new UserOrganizationModel();
        $dataInfo = $carModel->getInfoByValue($params);
        $passportUserInfo = [];
        if(isset($dataInfo['user_id']) && $dataInfo['user_id']) {
            $passportService = new PassportService();
            $passportUserInfoRest = $passportService->getUserInfoByUserId($dataInfo['user_id']);
            if(BaseService::checkRetIsOk($passportUserInfoRest)) {
                $passportUserInfo = BaseService::getRetData($passportUserInfoRest);
            }
        }
        $organizationInfo = [];
        if(isset($dataInfo['organization_id']) && $dataInfo['organization_id']) {
            $organizationService = new OrganizationService();
            $organizationParams[] = ['=', 'id', $dataInfo['organization_id']];
            $organizationInfoRest = $organizationService->getInfo($organizationParams);
            if(BaseService::checkRetIsOk($organizationInfoRest)) {
                $organizationInfo = BaseService::getRetData($organizationInfoRest);
            }
        }
        $levelInfo = [];
        if(isset($dataInfo['level_id']) && $dataInfo['level_id']) {
            $levelService = new LevelService();
            $levelParams[] = ['=', 'id', $dataInfo['level_id']];
            $levelInfoRest = $levelService->getInfo($levelParams);
            if(BaseService::checkRetIsOk($levelInfoRest)) {
                $levelInfo = BaseService::getRetData($levelInfoRest);
            }
        }
        if(!empty($dataInfo)) {
            $dataInfo['username'] = "";
            $dataInfo['nickname'] = "";
            $dataInfo['full_name'] = "";
            $dataInfo['avatar_img'] = "";
            $dataInfo['apply_organization_date'] = "";
            $dataInfo['join_organization_date'] = "";
            $dataInfo['native_place'] = "";
            $dataInfo['education'] = "";
            $dataInfo['user_status'] = "";
            $dataInfo['organization_title'] = "";
            $dataInfo['level_title'] = "";
            if(!empty($passportUserInfo)) {
                $dataInfo['username'] = isset($passportUserInfo['username']) ? $passportUserInfo['username'] : "";
                $dataInfo['nickname'] = isset($passportUserInfo['nickname']) ? $passportUserInfo['nickname'] : "";
                $dataInfo['full_name'] = isset($passportUserInfo['full_name']) ? $passportUserInfo['full_name'] : "";
                $dataInfo['avatar_img'] = isset($passportUserInfo['avatar_img']) ? $passportUserInfo['avatar_img'] : "";
                $dataInfo['apply_organization_date'] = isset($passportUserInfo['apply_organization_date']) ? $passportUserInfo['apply_organization_date'] : "";
                $dataInfo['join_organization_date'] = isset($passportUserInfo['join_organization_date']) ? $passportUserInfo['join_organization_date'] : "";
                $dataInfo['native_place'] = isset($passportUserInfo['native_place']) ? $passportUserInfo['native_place'] : "";
                $dataInfo['education'] = isset($passportUserInfo['education']) ? $passportUserInfo['education'] : "";
                $dataInfo['user_status'] = isset($passportUserInfo['user_status']) ? $passportUserInfo['user_status'] : "";
            }
            if(!empty($organizationInfo)) {
                $dataInfo['organization_title'] = isset($organizationInfo['title']) ? $organizationInfo['title'] : "";
            }
            if(!empty($levelInfo)) {
                $dataInfo['level_title'] = isset($levelInfo['title']) ? $levelInfo['title'] : "";
            }
        }
        unset($dataInfo['user_id']);
        unset($dataInfo['organization_id']);
        unset($dataInfo['level_id']);
        return BaseService::returnOkData($dataInfo);
    }
}
