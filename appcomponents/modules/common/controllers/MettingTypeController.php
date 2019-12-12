<?php
/**
 * 会议类型相关相关的接口
 * @文件名称: MettingTypeController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\MettingTypeService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class MettingTypeController extends UserBaseController
{
    public function beforeAction($action){
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
        $size = intval(Yii::$app->request->post('size', -1));
        $newsService = new MettingTypeService();
        $params = [];
        $params[] = ['=','status',1];
        return $newsService->getList($params, [], $page, $size,['id','title']);
    }

}
