<?php
/**
 * banner相关接口请求入口操作
 * @文件名称: BannerController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\BannerService;
use source\controllers\BaseController;
use Yii;
class BannerController extends BaseController
{
    public function beforeAction($action){
        return parent::beforeAction($action);
    }
    /**
     * 首页banner获取
     * @return array
     */
    public function actionGetBanners() {
        $bannerService = new BannerService();
        $params = [];
//        $type_id = intval(Yii::$app->request->post('type_id', 0));
//        $params[] = ['=', 'type_id', $type_id];
        $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
        return $bannerService->getBannerList($params, ['sort'=>SORT_ASC]);
    }
}
