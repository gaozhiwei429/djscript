<?php
/**
 * 产品功能相关的接口
 * @文件名称: UtilizationController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\UserUtilizationService;
use appcomponents\modules\common\UtilizationService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class UtilizationController extends UserBaseController
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
     * 首页功能列表获取
     * @return array
     */
    public function actionGetData() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', -1));
        $type = intval(Yii::$app->request->post('type', 0));
        $utilizationService = new UtilizationService();
        $params = [];
        $params[] = ['!=', 'status', 0];
        if($type) {
            $params[] = ['=', 'type', $type];
        }
        return $utilizationService->getList($params, ['sort'=>SORT_DESC], $page, $size,['id','title','icon','type']);
    }
    /**
     * 编辑功能
     * @return array
     */
    public function actionEdit() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $utilizationIdsArr = Yii::$app->request->post('utilization_ids', []);
        if(empty($utilizationIdsArr)) {
            return BaseService::returnErrData([], 59000,"请求参数异常");
        }
        if(!is_array($utilizationIdsArr)) {
            $utilizationIdsArr = json_decode($utilizationIdsArr,true);
        }
        $userUtilizationService = new UserUtilizationService();
        return $userUtilizationService->setUtilizationData($this->user_id, $utilizationIdsArr);
    }

}
