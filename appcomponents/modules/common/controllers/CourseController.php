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
use source\controllers\BaseController;
use source\manager\BaseService;
use Yii;
class CourseController extends BaseController
{
    public function beforeAction($action){
        return parent::beforeAction($action);
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
        $courseListRet = $newsService->getList($params, ['sort'=>SORT_DESC], $page, $size,['uuid','title','course_type_id','content','pic_url']);
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
        $uuid = trim(Yii::$app->request->post('uuid', null));
        if(empty($uuid)) {
            return BaseService::returnErrData([], 54000, "请求参数异常");
        }
        $newsService = new CourseService();
        $params = [];
        $params[] = ['=', 'uuid', $uuid];
        return $newsService->getInfo($params);
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
}
