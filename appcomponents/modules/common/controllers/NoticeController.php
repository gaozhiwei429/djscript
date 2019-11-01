<?php
/**
 * banner相关的接口
 * @文件名称: BannerController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\NoticeService;
use source\controllers\BaseController;
use source\manager\BaseService;
use Yii;
class NoticeController extends BaseController
{
    public function beforeAction($action){
        return parent::beforeAction($action);
    }
    /**
     * 公告列表数据获取
     * @return array
     */
    public function actionGetList() {
        $page = intval(Yii::$app->request->post('p', 1));
        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $size = intval(Yii::$app->request->post('size', 10));
        $noticeService = new NoticeService();
        $params = [];
        if($type_id) {
            $params[] = ['=', 'type_id', $type_id];
        }
        $params[] = ['!=', 'status', 0];
        return $noticeService->getList($params, ['id'=>SORT_DESC, 'sort'=>SORT_DESC], $page, $size,['type_id','pic_url','uuid','title']);
    }
    /**
     * 首页获取公告
     * @return array
     */
    public function actionGetIndexList() {
        $page = 1;
        $size = 3;
        $noticeService = new NoticeService();
        $params = [];
        $params[] = ['!=', 'status', 0];
        return $noticeService->getList($params, ['id'=>SORT_DESC, 'sort'=>SORT_DESC], $page, $size,['id','title']);
    }
    /**
     * 公告详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        $uuid = trim(Yii::$app->request->post('uuid', 0));
        if(empty($uuid)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $noticeService = new NoticeService();
        $params = [];
        $params[] = ['=', 'uuid', $uuid];
        $params[] = ['!=', 'status', 0];
        return $noticeService->getInfo($params,['pic_url','uuid','content','title','type_id','create_time']);
    }
}
