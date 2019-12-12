<?php
/**
 * 会议相关相关的接口
 * @文件名称: MettingController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\MettingService;
use appcomponents\modules\common\MettingTypeService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\manager\BaseService;
use Yii;
class MettingController extends UserBaseController
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
        $size = intval(Yii::$app->request->post('size', 10));
        $type_id = intval(Yii::$app->request->post('type_id', 0));
        $newsService = new MettingService();
        $params = [];
//        $params[] = ['!=', 'status', 0];
		if(!empty($type_id)) {
			$params[] = ['=', 'metting_type_id', $type_id];
		}
        $mettingListRet = $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,
            ['id','title','address','start_time','end_time','metting_type_id']
        );
        if(BaseService::checkRetIsOk($mettingListRet)) {
            $mettingList = BaseService::getRetData($mettingListRet);
            $mettingTypeList = [];
            if(isset($mettingList['dataList']) && !empty($mettingList['dataList'])) {
                $mettintTypeIds = [];
                foreach($mettingList['dataList'] as $dataInfo) {
                    if(isset($dataInfo['metting_type_id']) && !empty($dataInfo['metting_type_id'])){
                        $mettintTypeIds[] = $dataInfo['metting_type_id'];
                    }
                }
                if(!empty($mettintTypeIds)) {
                    $mettingTypeService = new MettingTypeService();
                    $mettingTypeParams[] = ['in', 'id', $mettintTypeIds];
                    $mettingTypeListRet = $mettingTypeService->getList($mettingTypeParams, [], 1, -1, ['id','title','status'], true);
                    $mettingTypeDataList = BaseService::getRetData($mettingTypeListRet);
                    if(isset($mettingTypeDataList['dataList']) && !empty($mettingTypeDataList['dataList'])) {
                        $mettingTypeList = $mettingTypeDataList['dataList'];
                    }
                }
                foreach($mettingList['dataList'] as &$dataInfo) {
                    $dataInfo['metting_type_title'] = "";
                    if(isset($dataInfo['metting_type_id']) && isset($mettingTypeList[$dataInfo['metting_type_id']])){
                        $dataInfo['metting_type_title'] = isset($mettingTypeList[$dataInfo['metting_type_id']]['title']) ?
                            $mettingTypeList[$dataInfo['metting_type_id']]['title'] : "";
                    }
                }
                return BaseService::returnOkData($mettingList);
            }
        }
        return $mettingListRet;
    }

    /**
     * 详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $newsService = new MettingService();
        $params[] = ['=', 'id', $id];
        $infoRet = $newsService->getInfo($params);
        $newsInfo = BaseService::getRetData($infoRet);
        if(BaseService::checkRetIsOk($infoRet)) {
            $passportSrvice = new PassportService();
            $president_userids = [];
            $speaker_userids = [];
            if(isset($newsInfo['president_userid'])) {
                $president_userids = explode(',', $newsInfo['president_userid']);
            }
            if(isset($newsInfo['speaker_userid'])) {
                $speaker_userids = explode(',', $newsInfo['speaker_userid']);
            }
            if(!empty($speaker_userids) || !empty($president_userids)) {
                $passportParams = [];
                $passportParams[] = ['in', 'id', array_unique(array_merge($president_userids,$speaker_userids))];
                $passportListRet = $passportSrvice->getList($passportParams, [], 1, -1, ['id'], true, true);
                $passportListData = BaseService::getRetData($passportListRet);
                $newsInfo['president_people'] = [];
                $newsInfo['speaker_people'] = [];
                if(!empty($passportListData) && isset($passportListData['dataList'])) {
                    if(!empty($president_userids)) {
                        foreach($president_userids as $president_userid) {
                            $newsInfo['president_people'][] = isset($passportListData['dataList'][$president_userid]) ? $passportListData['dataList'][$president_userid] : [];
                        }
                    }
                    if(!empty($speaker_userids)) {
                        foreach($speaker_userids as $president_userid) {
                            $newsInfo['speaker_people'][] = isset($passportListData['dataList'][$president_userid]) ? $passportListData['dataList'][$president_userid] : [];
                        }
                    }
                }
            }
            return BaseService::returnOkData($newsInfo);
        }
        return $infoRet;
    }

    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetStatus() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $status = intval(Yii::$app->request->post('status',  0));
        $newsService = new MettingService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['status'] = $status;
        return $newsService->editInfo($dataInfo);
    }

    /**
     * 详情数据状态编辑
     * @return array
     */
    public function actionSetSort() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = trim(Yii::$app->request->post('id', 0));
        $sort = intval(Yii::$app->request->post('sort',  0));
        $newsService = new MettingService();
        if(empty($id)) {
            return BaseService::returnErrData([], 58000, "请求参数异常，请填写完整");
        }
        $dataInfo['id'] = $id;
        $dataInfo['sort'] = $sort;
        return $newsService->editInfo($dataInfo);
    }
    /**
     * 详情数据编辑
     * @return array
     */
    public function actionEdit() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $title = trim(Yii::$app->request->post('title', ""));
        $address = trim(Yii::$app->request->post('address', ""));
        $content = trim(Yii::$app->request->post('content', ""));
        $status = intval(Yii::$app->request->post('status', 0));
        $sort = intval(Yii::$app->request->post('sort', 0));
        $startandenddate = trim(Yii::$app->request->post('startandenddate', null));
        $speaker_userid = trim(Yii::$app->request->post('speaker_userid', ""));
        $president_userid = trim(Yii::$app->request->post('president_userid', ""));
        $metting_type_id = intval(Yii::$app->request->post('metting_type_id', 0));
        $join_peoples = trim(Yii::$app->request->post('join_peoples', null));
        $organization_id = intval(Yii::$app->request->post('organization_id', 0));
        $mettingService = new MettingService();
        if(empty($title)) {
            return BaseService::returnErrData([], 55900, "考题名称不能为空");
        }
        $dataInfo = [];
        if(!empty($startandenddate)) {
            $startandenddateArr = explode(" - ", $startandenddate);
            if(!empty($startandenddateArr[0]) && isset($startandenddateArr[0])) {
                $dataInfo['start_time'] = $startandenddateArr[0];
            }
            if(!empty($startandenddateArr[1]) && isset($startandenddateArr[1])) {
                $dataInfo['end_time'] = $startandenddateArr[1];
            }
        }

        if(!empty($title)) {
            $dataInfo['title'] = $title;
        } else {
            $dataInfo['title'] = "";
        }
        if(!empty($address)) {
            $dataInfo['address'] = $address;
        } else {
            $dataInfo['address'] = "";
        }
        if(!empty($content)) {
            $dataInfo['content'] = $content;
        } else {
            $dataInfo['content'] = "";
        }
        if(!empty($president_userid)) {
            $dataInfo['president_userid'] = implode(',', array_unique(explode(',',$president_userid)));;
        } else {
            $dataInfo['president_userid'] = "";
        }
        if(!empty($join_peoples)) {
            $dataInfo['join_peoples'] = implode(',', array_unique(explode(',',$join_peoples)));;
        } else {
            $dataInfo['join_peoples'] = "";
        }
        if(!empty($speaker_userid)) {
            $dataInfo['speaker_userid'] = implode(',', array_unique(explode(',',$speaker_userid)));
        } else {
            $dataInfo['speaker_userid'] = "";
        }
        if(!empty($id)) {
            $dataInfo['id'] = $id;
        } else {
            $dataInfo['id'] = 0;
        }
        if(!empty($metting_type_id)) {
            $dataInfo['metting_type_id'] = $metting_type_id;
        } else {
            $dataInfo['metting_type_id'] = 0;
        }
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 58000, "提交数据有误");
        }
        $dataInfo['status'] = $status;
        $dataInfo['sort'] = $sort;
        $dataInfo['organization_id'] = $organization_id;
        $dataInfo['join_peoples'] = $join_peoples;
        return $mettingService->editInfo($dataInfo);
    }
}
