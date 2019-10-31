<?php
/**
 * banner相关的数据获取service
 * @文件名称: BannerService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\BannerModel;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class BannerService extends BaseService
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
     * C端Banner数据获取
     * @param $addData
     * @return array
     */
    public function getBannerList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $carModel = new BannerModel();
        $cityList = $carModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 获取banner详情数据
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        if(empty($params)) {
            return BaseService::returnErrData([], 55000, "请求参数异常");
        }
        $bannerModel = new BannerModel();
        $bannerInfo = $bannerModel->getInfoByValue($params);
        if(!empty($bannerInfo)) {
            return BaseService::returnOkData($bannerInfo);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 编辑banner详情数据
     * @param $params
     * @return array
     */
    public function editInfo($dataInfo) {
        if(empty($dataInfo)) {
            return BaseService::returnErrData([], 56900, "请求参数异常");
        }
        $bannerModel = new BannerModel();
        $id = isset($dataInfo['id']) ? $dataInfo['id'] : 0;
        $editRest = 0;
        if($id) {
            if(isset($dataInfo['id'])) {
                unset($dataInfo['id']);
            }
            $editRest = $bannerModel->updateInfo($id, $dataInfo);
        } else {
            $editRest = $bannerModel->addInfo($dataInfo);
        }
        if(!empty($editRest)) {
            return BaseService::returnOkData($editRest);
        }
        return BaseService::returnErrData([], 500, "操作异常");
    }
}
