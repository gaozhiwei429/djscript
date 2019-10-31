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
    public function actionGetIndexList() {
        $bannerService = new BannerService();
        $params = [];
        $p = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
//        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $params[] = ['=', 'type', 1];
        $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
        return $bannerService->getBannerList($params, ['sort'=>SORT_ASC], $p, $size,['pic_url','url','news_id','title']);
    }
    /**
     * 学习页面banner获取
     * @return array
     */
    public function actionGetStudyList() {
        $bannerService = new BannerService();
        $params = [];
        $p = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
//        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $params[] = ['=', 'type', 2];
        $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
        return $bannerService->getBannerList($params, ['sort'=>SORT_ASC], $p, $size,['pic_url','url','news_id','title']);
    }
    /**
     * 组织页面banner获取
     * @return array
     */
    public function actionGetGroupList() {
        $bannerService = new BannerService();
        $params = [];
        $p = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
//        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $params[] = ['=', 'type', 2];
        $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
        return $bannerService->getBannerList($params, ['sort'=>SORT_ASC], $p, $size,['pic_url','url','news_id','title']);
    }
}
