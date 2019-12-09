<?php
/**
 * 课程分类相关的数据获取service
 * @文件名称: CourseTypeService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\CourseTypeModel;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class CourseTypeService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\common\controllers';
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * C端资讯数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = -1, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $newsModel = new CourseTypeModel();
        $cityList = $newsModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 获取资讯详情数据
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        if(empty($params)) {
            return BaseService::returnErrData([], 55000, "请求参数异常");
        }
        $newsModel = new CourseTypeModel();
        $newsInfo = $newsModel->getInfoByValue($params);
        if(!empty($newsInfo)) {
            return BaseService::returnOkData($newsInfo);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 编辑资讯详情数据
     * @param $params
     * @return array
     */
    public function editInfo($dataInfo) {
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 56900, "请求参数异常");
        }
        $newsModel = new CourseTypeModel();
        $id = isset($dataInfo['id']) ? $dataInfo['id'] : 0;
        $editRest = 0;
        if($id) {
            if(isset($dataInfo['id'])) {
                unset($dataInfo['id']);
            }
            $editRest = $newsModel->updateInfo($id, $dataInfo);
        } else {
            $editRest = $newsModel->addInfo($dataInfo);
        }
        if(!empty($editRest)) {
            return BaseService::returnOkData($editRest);
        }
        return BaseService::returnErrData([], 500, "操作异常");
    }
    /**
     * 获取分类结构树
     * @param array $params
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @param bool $index
     * @return array
     */
    public function getTree($params = [], $orderBy = [], $p = 1, $limit = -1, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $newsModel = new CourseTypeModel();
        $typeList = $newsModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
        if(!empty($typeList)) {
            if(!$index) {
                return BaseService::returnOkData($typeList);
            }
            if(isset($typeList['dataList']) && !empty($typeList['dataList'])) {
                $dataList = Common::getTree($typeList['dataList']);
                return BaseService::returnOkData($dataList);
            }
            return BaseService::returnOkData($typeList);
        }
        return BaseService::returnErrData([], 53700, "获取树状结构数据不存在");
    }
}
