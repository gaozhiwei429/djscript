<?php
/**
 * 献花相关相关的接口
 * @文件名称: GiftIconController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\GiftIconService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class GiftIconController extends UserBaseController
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
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', -1));
        $type = intval(Yii::$app->request->post('type', 0));
        $newsService = new GiftIconService();
        $params = [];
        if(!$type) {
            return BaseService::returnErrData([], 53500, "请求参数异常");
        }
        $params[] = ['=', 'type', $type];
        $params[] = ['=', 'status', 1];
        return $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,['pic_url']);
    }
}
