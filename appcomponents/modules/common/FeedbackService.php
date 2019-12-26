<?php
/**
 * 产品的评论反馈信息数据service
 * @文件名称: FeedbackService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\FeedbackModel;
use appcomponents\modules\passport\PassportService;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class FeedbackService extends BaseService
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
        $feedbackModel = new FeedbackModel();
        $cityList = $feedbackModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 评论信息记录入库
     * @param $user_id 提交者用户id
     * @param $content 提交内容
     * @param int $type_id 提交对象的所属分类
     * @param int $object_id 提交对象的唯一标识
     * @param int $score 提交对象的打分数值
     * @return array
     */
    public function addData($user_id, $content, $object_id=0, $utilization_flag=0, $picUtl = []) {
        if(empty($object_id)) {
            return BaseService::returnErrData([], 55700, "请求参数异常");
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
        $dataInfo['content'] = $content;
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
        $dataInfo['pic_url'] = (is_array($picUtl) && !empty($picUtl)) ? json_encode($picUtl) : json_encode([]);
        $feedbackModel = new FeedbackModel();
        $feedback = $feedbackModel->addInfo($dataInfo);
        if($feedback) {
            return BaseService::returnOkData($feedback);
        }
        return BaseService::returnErrData([], 56700, "评论失败");
    }
}
