<?php
/**
 * 用户的首页产品包含的应用集合相关的数据获取service
 * @文件名称: UserUtilizationService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\UserUtilizationModel;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class UserUtilizationService extends BaseService
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
     * C端数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $newsModel = new UserUtilizationModel();
        $cityList = $newsModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 获取数据详情
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        $userUtilizationModel = new UserUtilizationModel();
        $ret = $userUtilizationModel->getInfoByValue($params);
        if(!empty($ret)) {
            return BaseService::returnOkData($ret);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 编辑详情数据
     * @param $params
     * @return array
     */
    public function editInfo($dataInfo) {
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 56900, "请求参数异常");
        }
        $userUtilizationModel = new UserUtilizationModel();
        $id = isset($dataInfo['id']) ? $dataInfo['id'] : 0;
        $editRest = 0;
        if($id) {
            if(isset($dataInfo['id'])) {
                unset($dataInfo['id']);
            }
            $editRest = $userUtilizationModel->updateInfo($id, $dataInfo);
        } else {
            $editRest = $userUtilizationModel->addInfo($dataInfo);
        }
        if(!empty($editRest)) {
            return BaseService::returnOkData($editRest);
        }
        return BaseService::returnErrData([], 500, "操作异常");
    }
    /**
     * 编辑首页显示功能集合
     * @param $user_id
     * @param array $utilizationIds
     * @return array
     */
    public function setUtilizationData($user_id, $utilizationIds=[]) {
        if(empty($user_id) || empty($utilizationIds) || !is_array($utilizationIds)) {
            return BaseService::returnErrData([], 54700, "请求参数异常");
        }
        $utilizationService = new UtilizationService();
        $utilizationParams[] = ['in', 'id', $utilizationIds];
        $utilizationRet = $utilizationService->getList($utilizationParams,[],1,-1,['id']);
        $utilizationData = BaseService::getRetData($utilizationRet);
        if(isset($utilizationData['count']) && $utilizationData['count']!=count($utilizationIds)){
            return BaseService::returnErrData([], 59800, "请求参数异常");
        }
        $params[] = ['=', 'user_id', $user_id];
        $ret = $this->getInfo($params);
        if(BaseService::checkRetIsOk($ret)) {
            $resultInfo = BaseService::getRetData($ret);
            $dataInfo['id'] = isset($resultInfo['id']) ? $resultInfo['id'] : "";
        }
        $dataInfo['utilization_ids'] = json_encode($utilizationIds);
        $dataInfo['user_id'] = $user_id;
        $dataInfo['status'] = 1;
        return $this->editInfo($dataInfo);
    }
    /**
     * 编辑首页显示功能集合
     * @param $user_id
     * @param array $utilizationIds
     * @return array
     */
    public function getUserUtilizationData($user_id) {
        if(empty($user_id)) {
            return BaseService::returnErrData([], 54700, "请求参数异常");
        }
        $utilizationParams[] = ['=', 'user_id', $user_id];
        $utilizationParams[] = ['=', 'status', 1];
        $utilizationRet = $this->getInfo($utilizationParams);
        $utilizationData = BaseService::getRetData($utilizationRet);
        if(isset($utilizationData['utilization_ids']) && $utilizationData['utilization_ids']){
            return BaseService::returnOkData(json_decode($utilizationData['utilization_ids'], true));
        }
        return BaseService::returnErrData([], 512900, "请求数据不存在");
    }
}
