<?php
/**
 * Type相关的数据获取service
 * @文件名称: TypeService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\TypeModel;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class TypeService extends BaseService
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
     * C端type数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $typeModel = new TypeModel();
        $params[] = ['=', 'status', $typeModel::ON_LINE_STATUS];
        $cityList = $typeModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * C端type数据获取
     * @param $addData
     * @return array
     */
    public function getInfo($params = []) {
        $typeModel = new TypeModel();
        $params[] = ['=', 'status', $typeModel::ON_LINE_STATUS];
        $typeInfo = $typeModel->getInfoByParams($params);
        if(!empty($typeInfo)) {
            return BaseService::returnOkData($typeInfo);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    public function getTree($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $typeModel = new TypeModel();
        $typeList = $typeModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
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
