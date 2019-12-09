<?php
/**
 * 党课分类相关接口
 * @文件名称: CourseTypeController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\CourseTypeService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class CourseTypeController extends UserBaseController
{
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 获取树状管理结构数据
     * @return array
     */
    public function actionGetTrees() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', -1));
        $typeService = new CourseTypeService();
        $params = [];
        $params[] = ['!=', 'status', 1];
        return $typeService->getTree($params, ['id'=>SORT_DESC, 'sort'=>SORT_DESC], $page, $size, $fied=['*'], true);
    }

    /**
     * 详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        $id = intval(Yii::$app->request->post('id', 0));
        if(empty($id)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $typeService = new CourseTypeService();
        $params = [];
        $params[] = ['=', 'id', $id];
        return $typeService->getInfo($params);
    }
    /**
     * 详情数据编辑
     * @return array
     */
    public function actionEdit() {
        $post = Yii::$app->request->post();
        $id = intval(Yii::$app->request->post('id', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $use_id = trim(Yii::$app->request->post('use_id', ""));
        $icon = trim(Yii::$app->request->post('icon', ""));
        $parent_id = intval(Yii::$app->request->post('parent_id',  0));
        $typeService = new CourseTypeService();
        if(empty($title) || !isset($post['parent_id'])) {
            return BaseService::returnErrData([], 56400, "请求参数异常，请填写完整");
        }
        $dataInfo = [];
        if(!empty($sort)) {
            $dataInfo['sort'] = $sort;
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        }
        if(!empty($title)) {
            $dataInfo['title'] = $title;
        }
        if(!empty($use_id)) {
            $dataInfo['use_id'] = $use_id;
        }
        if(!empty($icon)) {
            $dataInfo['icon'] = $icon;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        if($parent_id) {
            $typeInfoParams[] = ['=', 'id', $parent_id];
            $typeInfoRet = $typeService->getInfo($typeInfoParams);
            if(!BaseService::checkRetIsOk($typeInfoRet)) {
                return BaseService::returnErrData([], 58600, "请求参数异常");
            }
        }
        $dataInfo['parent_id'] = $parent_id;
        $dataInfo['status'] = $status;
        return $typeService->editInfo($dataInfo);
    }
    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetStatus() {
        $id = intval(Yii::$app->request->post('id', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $typeService = new CourseTypeService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['status'] = $status;
        return $typeService->editInfo($dataInfo);
    }
}
