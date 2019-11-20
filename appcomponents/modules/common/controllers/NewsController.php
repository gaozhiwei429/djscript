<?php
/**
 * 资讯相关相关的接口
 * @文件名称: NewsController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\NewsService;
use appcomponents\modules\common\TypeService;
use source\controllers\BaseController;
use source\manager\BaseService;
use Yii;
class NewsController extends BaseController
{
    public function beforeAction($action){
        return parent::beforeAction($action);
    }
    /**
     * 首页资讯列表获取
     * @return array
     */
    public function actionGetIndexList() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $newsService = new NewsService();
        $params = [];
        $params[] = ['!=', 'status', 0];
        return $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,['pic_url','uuid','title','type_id','create_time']);
    }
    /**
     * 资讯列表数据获取
     * @return array
     */
    public function actionGetList() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $newsService = new NewsService();
        $params = [];
        $params[] = ['!=', 'status', 0];
        if($type_id) {
            $typeService = new TypeService();
            $typeParams[] = ['=', 'id', $type_id];
            $typeRet = $typeService->getInfo($typeParams);
            $typeInfo = BaseService::getRetData($typeRet);
            if(isset($typeInfo['parent_id']) && $typeInfo['parent_id']==0) {
                $listParams[] = ['=', 'parent_id', $typeInfo['id']];
                $typeListRet = $typeService->getList($listParams,[], 1, -1,['id']);
                $typeList = BaseService::getRetData($typeListRet);
                $typeDataList = isset($typeList['dataList']) ? $typeList['dataList'] : [];
                if(!empty($typeDataList)) {
                    $typeIds = array_column($typeDataList, 'id');
                    $typeIds[] = $type_id;
                    $params[] = ['in', 'type_id', $typeIds];
                }
            } else {
                $params[] = ['=', 'type_id', $type_id];
            }
        }
        return $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,['pic_url','uuid','title','type_id','create_time']);
    }

    /**
     * 资讯详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        $uuid = trim(Yii::$app->request->post('uuid', 0));
        if(empty($uuid)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $newsService = new NewsService();
        $params = [];
        $params[] = ['=', 'uuid', $uuid];
        $params[] = ['!=', 'status', 0];
        return $newsService->getInfo($params,['pic_url','uuid','content','title','type_id','create_time']);
    }
}
