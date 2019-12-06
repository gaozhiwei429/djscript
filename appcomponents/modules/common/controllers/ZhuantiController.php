<?php
/**
 * 专题资讯相关的接口
 * @文件名称: ZhuantiController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\ZhuantiService;
use appcomponents\modules\common\ZhuantiTypeService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class ZhuantiController extends UserBaseController
{
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 首页资讯获取
     * @return array
     */
    public function actionGetList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $utilization_id = intval(Yii::$app->request->post('utilization_id', 0));
        $zhuanti_type_id = intval(Yii::$app->request->post('zhuanti_type_id', 0));

        $params = [];
        $newsService = new ZhuantiService();
        if($utilization_id && !$zhuanti_type_id) {
            $typeIds = [];
            $zhuantiTypeService = new ZhuantiTypeService();
            $zhuantiTypeParams[] = ['=', 'utilization_id', $utilization_id];
            $zhuantiTypeDataListRet = $zhuantiTypeService->getList($zhuantiTypeParams,[],1,-1,['id']);
            if(BaseService::checkRetIsOk($zhuantiTypeDataListRet)) {
                $zhuantiTypeDataList = BaseService::getRetData($zhuantiTypeDataListRet);
                if(isset($zhuantiTypeDataList['dataList']) && !empty($zhuantiTypeDataList['dataList'])) {
                    foreach($zhuantiTypeDataList['dataList'] as $zhuantiTypeData) {
                        if(isset($zhuantiTypeData['id'])) {
                            $typeIds[] = $zhuantiTypeData['id'];
                        }
                    }
                }
            }
            if(!empty($typeIds)) {
                $params[] = ['in', 'zhuanti_type_id', $typeIds];
            }
        }

        return $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size, ['id','title','pic_url', 'zhuanti_type_id','create_time']);
    }

    /**
     * 文章详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        if(empty($id)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $newsService = new ZhuantiService();
        $params = [];
        $params[] = ['=', 'id', $id];
        return $newsService->getInfo($params);
    }
    /**
     * 资讯详情数据编辑
     * @return array
     */
    public function actionEdit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $content = trim(Yii::$app->request->post('content', ""));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $zhuanti_type_id = intval(Yii::$app->request->post('zhuanti_type_id',  0));
        $newsService = new ZhuantiService();
        $postData = Yii::$app->request->post();
        $pic_urlArr = [];
        foreach($postData as $k=>$pic_url) {
            if(strstr($k,"pic_url")) {
                $pic_urlArr[] = $pic_url;
            }
        }
        if(empty($title)) {
            return BaseService::returnErrData([], 55900, "请求参数异常，请填写完整");
        }
        $dataInfo = [];
        if(!empty($pic_urlArr)) {
            $dataInfo['pic_url'] = $pic_urlArr ? json_encode($pic_urlArr) : "[]";
        }
        if(!empty($user_id)) {
            $dataInfo['user_id'] = $user_id;
        }
        if(!empty($title)) {
            $dataInfo['title'] = $title;
        } else {
            $dataInfo['title'] = "";
        }
        if(!empty($content)) {
            $dataInfo['content'] = $content;
        } else {
            $dataInfo['content'] = "";
        }
        if(!empty($sort)) {
            $dataInfo['sort'] = $sort;
        } else {
            $dataInfo['sort'] = 0;
        }
        if(!empty($zhuanti_type_id)) {
            $dataInfo['zhuanti_type_id'] = $zhuanti_type_id;
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['status'] = $status;
        return $newsService->editInfo($dataInfo);
    }
    /**
     * 获取分类
     * @return array
     */
    public function actionGetTypes() {
        $utilization_id = intval(Yii::$app->request->post('utilization_id', 0));
        if(!$utilization_id) {
            return BaseService::returnErrData([], 515000, "请求参数异常");
        }
        $zhuantiTypeService = new ZhuantiTypeService();
        $zhuantiTypeParams[] = ['=', 'utilization_id', $utilization_id];
        return $zhuantiTypeService->getList($zhuantiTypeParams,[],1,-1,['id','title']);
    }
}
