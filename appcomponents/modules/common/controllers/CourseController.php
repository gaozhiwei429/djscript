<?php
/**
 * 课程相关相关的接口
 * @文件名称: CourseController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\CourseService;
use appcomponents\modules\common\CourseTypeService;
use appcomponents\modules\common\LessionService;
use appcomponents\modules\common\SectionsService;
use appcomponents\modules\common\UserCourseService;
use appcomponents\modules\common\UserOrganizationService;
use appcomponents\modules\common\UserStudyService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class CourseController extends UserBaseController
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
    public function actionGetBannerList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 5));
        $userCourseService = new UserCourseService();
        $params = [];
        $params[] = ['=', 'is_del', 0];
        $params[] = ['=', 'user_id', $this->user_id];
        $ret = $userCourseService->getList($params, ['id'=>SORT_DESC], $page, $size,['*']);
        if(BaseService::checkRetIsOk($ret)) {
            $dataList = BaseService::getRetData($ret);
            $courseIds = [];
            if(isset($dataList['dataList']) && !empty($dataList['dataList'])) {
                foreach($dataList['dataList'] as $dataInfo) {
                    if(isset($dataInfo['course_id'])) {
                        $courseIds[] = $dataInfo['course_id'];
                    }
                }
                if(!empty($courseIds)) {
                    $courseService = new CourseService();
                    $courseParams[] = ['in', 'id', $courseIds];
                    $courseListRet = $courseService->getList($courseParams, [], 1, count($courseIds),
                        ['id','title', 'pic_url', 'sections_count', 'lessions_count', 'elective_type'], true
                    );
                    if(BaseService::checkRetIsOk($courseListRet)) {
                        $courseListData = BaseService::getRetData($courseListRet);
                        $courseList = isset($courseListData['dataList']) ? $courseListData['dataList'] : [];
                        foreach($dataList['dataList'] as &$dataInfo) {
                            $dataInfo['title'] = "";
                            $dataInfo['pic_url'] = "";
                            $dataInfo['elective_type'] = 1;
                            $dataInfo['sections_count'] = 0;
                            $dataInfo['lessions_count'] = 0;
                            if(isset($dataInfo['course_id'])) {
                                $dataInfo['title'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['title']))
                                    ? $courseList[$dataInfo['course_id']]['title'] : "";
                                $dataInfo['pic_url'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['pic_url']))
                                    ? $courseList[$dataInfo['course_id']]['pic_url'] : "";
                                $dataInfo['elective_type'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['elective_type']))
                                    ? $courseList[$dataInfo['course_id']]['elective_type'] : 1;
                                $dataInfo['sections_count'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['sections_count']))
                                    ? $courseList[$dataInfo['course_id']]['sections_count'] : 0;
                                $dataInfo['lessions_count'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['lessions_count']))
                                    ? $courseList[$dataInfo['course_id']]['lessions_count'] : 0;
                            }
                        }
                    }
                }
            }
            return BaseService::returnOkData($dataList);
        }
        return $ret;
    }

    /**
     * 首页资讯获取
     * @return array
     */
    public function actionGetList() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $course_type_id = intval(Yii::$app->request->post('course_type_id', 0));
        $newsService = new CourseService();
        $params = [];
        if($course_type_id) {
            $params[] = ['=', 'course_type_id', $course_type_id];
        }
        $courseListRet = $newsService->getList($params, ['id'=>SORT_DESC], $page, $size,['uuid','title','course_type_id','content','pic_url','elective_type','sections_count','lessions_count']);
        if(BaseService::checkRetIsOk($courseListRet)) {
            $courseList = BaseService::getRetData($courseListRet);
            if(!empty($courseList['dataList'])) {
                $courseTypeService = new CourseTypeService();
                $courseTypeDataRet = $courseTypeService->getList([], [], 1, -1, ['*'], true);
                if(BaseService::checkRetIsOk($courseTypeDataRet)) {
                    $courseTypeDataResult = BaseService::getRetData($courseTypeDataRet);
                    $courseTypeData = isset($courseTypeDataResult['dataList']) ? $courseTypeDataResult['dataList'] : [];
                }
                foreach($courseList['dataList'] as $k=>&$v) {
                    $v['course_type_title'] = "未知";
                    if(isset($v['course_type_id']) && isset($courseTypeData[$v['course_type_id']])) {
                        $v['course_type_title'] = isset($courseTypeData[$v['course_type_id']]['title']) ? $courseTypeData[$v['course_type_id']]['title'] : "";
                    }
                }
            }
            return BaseService::returnOkData($courseList);
        }
        return $courseListRet;
    }
    /**
     * 文章详情数据获取
     * @return array
     */
    public function actionGetInfo() {
        $id = trim(Yii::$app->request->post('id', null));
        if(empty($uuid)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $newsService = new CourseService();
        $params = [];
        $params[] = ['=', 'id', $id];
        $courseInfoRet = $newsService->getInfo($params);
        if(BaseService::checkRetIsOk($courseInfoRet)) {
            $courseInfo = BaseService::getRetData($courseInfoRet);
            if(isset($courseInfo['sections_ids']) && !empty($courseInfo['sections_ids'])) {
                $sections_ids = explode(',',$courseInfo['sections_ids']);
                $sectionService = new SectionsService();
                $sectionParams[] = ['in', 'id', $sections_ids];
                $sectionParams[] = ['!=', 'status', 0];
                $sectionListRet = $sectionService->getList($sectionParams, ['sort'=>SORT_DESC, 'id'=>SORT_ASC], 1, -1, ['id', 'title', 'lession_ids'], true);
                $sectionDataList = BaseService::getRetData($sectionListRet);
                if(isset($sectionDataList['dataList']) && !empty($sectionDataList['dataList'])) {
                    foreach($sectionDataList['dataList'] as &$sectionData) {
                        $courseInfo['sectionData'][$sectionData['id']]['sectionInfo'] = $sectionData;
                        if(isset($sectionData['lession_ids']) && !empty($sectionData['lession_ids'])) {
                            $lessionParams = [];
                            $lessionParams[] = ['in', 'id', explode(',',$sectionData['lession_ids'])];
                            $lessionParams[] = ['!=', 'status', 0];
                            $lessionService = new LessionService();
                            $lessionListRet = $lessionService->getList($lessionParams, ['sort'=>SORT_ASC, 'id'=>SORT_ASC], 1, -1, ['id', 'title', 'uuid', 'file', 'format', 'duration']);
                            $lessionDataList = BaseService::getRetData($lessionListRet);
                            if(isset($lessionDataList['dataList']) && !empty($lessionDataList['dataList'])) {
                                $courseInfo['sectionData'][$sectionData['id']]['lessionList'] = $lessionDataList['dataList'];
                            }
                        }

                    }
                }
            }
            return BaseService::returnOkData($courseInfo);
        }
        return $courseInfoRet;
    }

    /**
     * 获取学习课程相关的分类
     * @return array
     */
    public function actionGetTreeType() {
        $courseTypeService = new CourseTypeService();
        $params = [];
        $params[] = ['=', 'status', 1];
        return $courseTypeService->getTree($params, $orderBy = ['sort'=>SORT_DESC], 1, -1, ['title','id','parent_id'], true);
    }
    /**
     * 课程学习
     * @return array
     */
    public function actionStudy() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $course_id = intval(Yii::$app->request->post('course_id', 0));
        $section_id = intval(Yii::$app->request->post('section_id', 0));
        $lession_id = intval(Yii::$app->request->post('lession_id', 0));
        $start_time = trim(Yii::$app->request->post('start_time', ""));
        $end_time = trim(Yii::$app->request->post('end_time', ""));
        if(empty($course_id) || empty($section_id) || empty($lession_id)) {
            return BaseService::returnErrData([], 514200, "请求参数异常");
        }
        $second = strtotime($end_time) - strtotime($start_time);
        $dataInfo = [];
        $lessionInfo = [];
        $duration = 0;
        if(!empty($lession_id)) {
            $lessionParams = [];
            $lessionParams[] = ['=', 'id', $lession_id];
            $lessionService = new LessionService();
            $lessionInfoRet = $lessionService->getInfo($lessionParams);
            $lessionInfo = BaseService::getRetData($lessionInfoRet);
            if(!empty($lessionInfo)){
                $duration = isset($lessionInfo['duration']) ? $lessionInfo['duration'] : 0;
            }
        } else {
            return BaseService::returnErrData([], 512400, "当前课件数据不存在");
        }
        $userOrganizationService = new UserOrganizationService();
        $passportService = new PassportService();
        $userStudyService = new UserStudyService();
        $userStudyParams[] = ['=', 'course_id', $course_id];
        $userStudyParams[] = ['=', 'section_id', $section_id];
        $userStudyParams[] = ['=', 'lession_id', $lession_id];
        $userStudyParams[] = ['=', 'user_id', $this->user_id];
        $userStudyInfoRet = $userStudyService->getInfo($userStudyParams);
        if(BaseService::checkRetIsOk($userStudyInfoRet)) {
            $userStudyInfo = BaseService::getRetData($userStudyInfoRet);
            $dataInfo['id'] = isset($userStudyInfo['id']) ? $userStudyInfo['id'] : 0;
            $start_time = isset($userStudyInfo['start_time']) ? $userStudyInfo['start_time'] : date("Y-m-d H:i:s");
            if(isset($userStudyInfo['second'])) {
                if($duration*60 <=($userStudyInfo['second']+$second)) {
                    $dataInfo['status'] = 1;
                }
                $dataInfo['second'] = $userStudyInfo['second']+$second;
            }
        } else {
            if($duration*60 <=$second) {
                $dataInfo['status'] = 1;
//                $dataInfo['second'] = $duration*60;
            }
            $dataInfo['second'] = $second;
        }
        $dataInfo['lession_duration'] = $duration;
        //获取当前用户所属的党组织id
        $userOrganizationParams = [];
        $userOrganizationParams[] = ['=', 'user_id', $this->user_id];
        $userOrganizationParams[] = ['=', 'status', 1];
        $userOrganizationInfoRet = $userOrganizationService->getInfo($userOrganizationParams);
        $userOrganizationInfo = BaseService::getRetData($userOrganizationInfoRet);
        $userInfoParams = [];
        $userInfoParams[] = ['=', 'user_id', $this->user_id];
        $passportInfoRet = $passportService->getUserInfoByParams($userInfoParams);
        $passportInfo = BaseService::getRetData($passportInfoRet);
        $dataInfo['course_id'] = $course_id;
        $dataInfo['section_id'] = $section_id;
        $dataInfo['lession_id'] = $lession_id;
        $dataInfo['user_id'] = $this->user_id;
        $dataInfo['full_name'] = isset($passportInfo['full_name']) ? $passportInfo['full_name'] : "";
        $dataInfo['avatar_img'] = isset($passportInfo['avatar_img']) ? $passportInfo['avatar_img'] : "";
        $dataInfo['organization_id'] = isset($lessionInfo['organization_id']) ? $lessionInfo['organization_id'] : 0;
        $dataInfo['user_organization_id'] = isset($userOrganizationInfo['organization_id']) ? $userOrganizationInfo['organization_id'] : 0;
        $dataInfo['user_level_id'] = isset($userOrganizationInfo['level_id']) ? $userOrganizationInfo['level_id'] : 0;
        $dataInfo['start_time'] = $start_time;
        $dataInfo['end_time'] = $end_time;
        return $userStudyService->editInfo($dataInfo);
    }
    /**
     * 我的课程列表数据获取
     * @return array
     */
    public function actionGetMyList() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $status = intval(Yii::$app->request->post('status', 0));
        $userCourseService = new UserCourseService();
        $params = [];
        $params[] = ['=', 'is_del', 0];
        $params[] = ['=', 'status', $status];
        $params[] = ['=', 'user_id', $this->user_id];
        $ret = $userCourseService->getList($params, ['id'=>SORT_DESC], $page, $size,['*']);
        if(BaseService::checkRetIsOk($ret)) {
            $dataList = BaseService::getRetData($ret);
            $courseIds = [];
            if(isset($dataList['dataList']) && !empty($dataList['dataList'])) {
                foreach($dataList['dataList'] as $dataInfo) {
                    if(isset($dataInfo['course_id'])) {
                        $courseIds[] = $dataInfo['course_id'];
                    }
                }
                if(!empty($courseIds)) {
                    $courseService = new CourseService();
                    $courseParams[] = ['in', 'id', $courseIds];
                    $courseListRet = $courseService->getList($courseParams, [], 1, count($courseIds),
                        ['id','title', 'pic_url', 'sections_count', 'lessions_count', 'elective_type'], true
                    );
                    if(BaseService::checkRetIsOk($courseListRet)) {
                        $courseListData = BaseService::getRetData($courseListRet);
                        $courseList = isset($courseListData['dataList']) ? $courseListData['dataList'] : [];
                        foreach($dataList['dataList'] as &$dataInfo) {
                            $dataInfo['title'] = "";
                            $dataInfo['pic_url'] = "";
                            $dataInfo['elective_type'] = 1;
                            $dataInfo['sections_count'] = 0;
                            $dataInfo['lessions_count'] = 0;
                            if(isset($dataInfo['course_id'])) {
                                $dataInfo['title'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['title']))
                                    ? $courseList[$dataInfo['course_id']]['title'] : "";
                                $dataInfo['pic_url'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['pic_url']))
                                    ? $courseList[$dataInfo['course_id']]['pic_url'] : "";
                                $dataInfo['elective_type'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['elective_type']))
                                    ? $courseList[$dataInfo['course_id']]['elective_type'] : 1;
                                $dataInfo['sections_count'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['sections_count']))
                                    ? $courseList[$dataInfo['course_id']]['sections_count'] : 0;
                                $dataInfo['lessions_count'] = (isset($courseList[$dataInfo['course_id']]) && isset($courseList[$dataInfo['course_id']]['lessions_count']))
                                    ? $courseList[$dataInfo['course_id']]['lessions_count'] : 0;
                            }
                        }
                    }
                }
            }
            return BaseService::returnOkData($dataList);
        }
    }
    /**
     * 加入我的课程
     * @return array
     */
    public function actionJoin() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $course_id = intval(Yii::$app->request->post('course_id', 0));
        $courseService = new CourseService();
        $params = [];
        $params[] = ['=', 'id', $course_id];
        $params[] = ['=', 'status', 1];
        $courseInfoRet = $courseService->getInfo($params);
        if(!BaseService::checkRetIsOk($courseInfoRet)) {
            return BaseService::returnErrData([], 533600, "当前课程不存在");
        }
        $courseInfo = BaseService::getRetData($courseInfoRet);
        if($courseInfo['elective_type']==2) {
            return BaseService::returnErrData([], 534000, "当前课程为必修课，无需加入");
        }
        $userCourseService = new UserCourseService();
        $userCourseParams = [];
        $userCourseParams[] = ['=', 'course_id', $course_id];
        $userCourseParams[] = ['=', 'user_id', $this->user_id];
        $ret = $userCourseService->getInfo($userCourseParams);
        if(BaseService::checkRetIsOk($ret)) {
            $userCourseInfo = BaseService::getRetData($ret);
            if(!empty($userCourseInfo) && $userCourseInfo['is_del']==1) {
                $editData = [
                    'id'=>isset($userCourseInfo['id']) ? $userCourseInfo['id'] : 0,
                    'is_del'=>0,
                ];
                return $userCourseService->editInfo($editData);
            }
        }
        $userCourseData = [
            'course_id' =>$course_id,
            'user_id' =>$this->user_id,
            'lessions_count' =>isset($courseInfo['lessions_count']) ? $courseInfo['lessions_count'] : 0,
            'elective_type' =>isset($courseInfo['elective_type']) ? $courseInfo['elective_type'] : 1,
        ];
        return $userCourseService->editInfo($userCourseData);
    }
    /**
     * 移除我的课程
     * @return array
     */
    public function actionRemove() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $course_id = intval(Yii::$app->request->post('course_id', 0));
        $courseService = new CourseService();
        $params = [];
        $params[] = ['=', 'id', $course_id];
        $params[] = ['=', 'status', 1];
        $courseInfoRet = $courseService->getInfo($params);
        if(!BaseService::checkRetIsOk($courseInfoRet)) {
            return BaseService::returnErrData([], 533600, "当前课程不存在");
        }
        $courseInfo = BaseService::getRetData($courseInfoRet);
        if($courseInfo['elective_type']==2) {
            return BaseService::returnErrData([], 534000, "当前课程为必修课，无需加入");
        }
        $userCourseService = new UserCourseService();
        $userCourseParams = [];
        $userCourseParams[] = ['=', 'course_id', $course_id];
        $userCourseParams[] = ['=', 'user_id', $this->user_id];
        $ret = $userCourseService->getInfo($userCourseParams);
        if(BaseService::checkRetIsOk($ret)) {
            $userCourseInfo = BaseService::getRetData($ret);
            if(!empty($userCourseInfo)) {
                $editData = [
                    'id'=>isset($userCourseInfo['id']) ? $userCourseInfo['id'] : 0,
                    'is_del'=>1,
                ];
                return $userCourseService->editInfo($editData);
            }
        }
        return $ret;
    }
    /**
     * 检查课程是否加入我的课程
     * @return array
     */
    public function actionCheckJoin() {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $course_id = intval(Yii::$app->request->post('course_id', 0));
        $courseService = new CourseService();
        $params = [];
        $params[] = ['=', 'id', $course_id];
        $params[] = ['=', 'status', 1];
        $courseInfoRet = $courseService->getInfo($params);
        if(!BaseService::checkRetIsOk($courseInfoRet)) {
            return BaseService::returnErrData([], 533600, "当前课程不存在");
        }
        $courseInfo = BaseService::getRetData($courseInfoRet);
        if($courseInfo['elective_type']==2) {
            return BaseService::returnErrData([], 534000, "当前课程为必修课，无需检查");
        }
        $userCourseService = new UserCourseService();
        $userCourseParams = [];
        $userCourseParams[] = ['=', 'course_id', $course_id];
        $userCourseParams[] = ['=', 'user_id', $this->user_id];
        $userCourseParams[] = ['=', 'is_del', 0];
        $ret = $userCourseService->getInfo($userCourseParams);
        if(BaseService::checkRetIsOk($ret)) {
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData([], 543400, "没有加入课程");
    }
    /**
     * 正在学习多少课，已完成多少课，累计学习时长
     * @return array
     */
    public function actionGetTotal() {
        $data['totalCourse'] = 0;
        $data['completedCourse'] = 0;
        $data['studyHour'] = "0小时";
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnOkData($data);
        }
        $userStudyParams = [];
        $userStudyParams[] = ['=', 'user_id', $this->user_id];
        $userStudyParams[] = ['=', 'status', 0];
        $userCourseService = new UserCourseService();
        $userStudyService = new UserStudyService();
        $userStudyRet = $userCourseService->getCount($userStudyParams);

        $userCourseParams1 = [];
        $userCourseParams1[] = ['=', 'user_id', $this->user_id];
        $userCourseParams1[] = ['=', 'status', 1];
        $userCourseRet = $userCourseService->getCount($userCourseParams1);
        $userStudyParams1 = [];
        $userStudyParams1[] = ['=', 'user_id', $this->user_id];
        $userStudNumRet = $userStudyService->getNum($userStudyParams1,['sum(second) as second']);
        $userStudyData = BaseService::getRetData($userStudyRet);
        $userCourseData = BaseService::getRetData($userCourseRet);
        $userStudNumData = BaseService::getRetData($userStudNumRet);
        if(!empty($userStudyData)) {
            $data['totalCourse'] = intval($userStudyData);
        } else {
            $data['totalCourse'] = 0;
        }
        if(!empty($userCourseData)) {
            $data['completedCourse'] = intval($userStudyData);
        } else {
            $data['completedCourse'] = 0;
        }
        if(!empty($userStudNumData)) {
            $data['studyHour'] = (isset($userStudNumData[0]) && isset($userStudNumData[0]['second'])) ?
            Common::getSecondHM($userStudNumData[0]['second']) : 0;
        } else {
            $data['studyHour'] = 0;
        }
        return BaseService::returnOkData($data);
    }
}
