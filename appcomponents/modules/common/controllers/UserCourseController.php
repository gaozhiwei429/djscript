<?php
/**
 * 用户所选课程相关相关的接口
 * @文件名称: UserCourseController
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\CourseService;
use appcomponents\modules\common\UserCourseService;
use appcomponents\modules\common\UserMettingService;
use appcomponents\modules\passport\PassportService;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class UserCourseController extends UserBaseController
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
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', 10));
        $userCourseService = new UserCourseService();
        $params = [];
        $params[] = ['=', 'is_del', 0];
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

}
