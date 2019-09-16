<?php

namespace appcomponents\modules\common\controllers;

use appcomponents\modules\common\CommonService;
use appcomponents\modules\common\models\AddrAreaModel;
use appcomponents\modules\common\models\AddrCityModel;
use appcomponents\modules\common\models\AddrProvinceModel;
use source\controllers\BaseController;
use source\controllers\UserBaseController;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
/**
 * 收货地址
 * @package appcomponents\modules\common\controllers
 */
class AddressController extends UserBaseController
{
    /**
     * 用户登录态基础类验证
     * @return array
     */
    public function beforeAction($action){
        $this->noLogin = false;
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 获取列表数据接口
     * @return array
     */
    public function actionGetList() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $p = intval(Yii::$app->request->post('p', 1));//当前第几页
        $size = intval(Yii::$app->request->post('size', 10));//每页显示多少条
        $status = intval(Yii::$app->request->post('status', 1));//状态【1已开启，0未开启】
        $params[] = ['=', 'user_id', $this->user_id];
        $params[] = ['=', 'status', $status ];
        $commonService = new CommonService();
        return $commonService->getAddressList($params, [], $p, $size, ['*'], true);
    }
    /**
     * 创建收货地址
     * @return array
     */
    public function actionEdit() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));//收货地址唯一标识
        $province_id = intval(Yii::$app->request->post('province_id', 0));//收货省份
        $city_id = intval(Yii::$app->request->post('city_id', 0));//收货地址城市
        $area_id = intval(Yii::$app->request->post('area_id', 0));//收货地址区县id
        $address = trim(Yii::$app->request->post('address', ""));//收货地址
        $full_user_name = trim(Yii::$app->request->post('full_user_name', ""));//签收人
        $full_user_mobile = trim(Yii::$app->request->post('full_user_mobile', ""));//签收人手机号
        $is_default = intval(Yii::$app->request->post('is_default', 0));//是否默认
        $commonService = new CommonService();
        $addressData = [];
        if(!$id) {
            if(empty($province_id) || empty($city_id) || empty($area_id) ||
                empty($address) || empty($full_user_name) || empty($full_user_mobile)) {
                return BaseService::returnErrData([], 56000, "请求参数有误");
            }
            $provinceParams[] = ['=', 'id', $province_id];
            $provinceInfoRet = $commonService->getProvinceInfo($provinceParams);
            $cityParams[] = ['=', 'id', $city_id];
            $cityInfoRet = $commonService->getCityInfo($cityParams);
            $areaParams[] = ['=', 'id', $area_id];
            $areaInfoRet = $commonService->getAreaInfo($areaParams);
            if(!BaseService::checkRetIsOk($provinceInfoRet)) {
                return BaseService::returnErrData([], 57200, "省份数据参数异常");
            }
            if(!BaseService::checkRetIsOk($cityInfoRet)) {
                return BaseService::returnErrData([], 57500, "城市数据参数异常");
            }
            if(!BaseService::checkRetIsOk($areaInfoRet)) {
                return BaseService::returnErrData([], 57800, "区县数据参数异常");
            }
            if(!Common::pregPhonNum($full_user_mobile)) {
                return BaseService::returnErrData([], 58200, "手机号格式输入有误");
            }
        }
        if($is_default) {
            $whereParams['user_id'] = $this->user_id;
            $updateParams['is_default'] = 0;
            $commonService->updateAllDataList($whereParams, $updateParams);
        }
        $addressData = [];
        $addressData['id'] = $id;
        if($province_id) {
            $addressData['province_id'] = $province_id;
        }
        if($city_id) {
            $addressData['city_id'] = $city_id;
        }
        if($area_id) {
            $addressData['area_id'] = $area_id;
        }
        if($address) {
            $addressData['address'] = $address;
        }
        if($full_user_name) {
            $addressData['full_user_name'] = $full_user_name;
        }
        if($full_user_mobile) {
            $addressData['full_user_mobile'] = $full_user_mobile;
        }
        $addressData['user_id'] = $this->user_id;
        $addressData['is_default'] = $is_default;
        return $commonService->editAddress($addressData);
    }
    /**
     * 获取详情数据接口
     * @return array
     */
    public function actionGetInfo() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $params[] = ['=', 'user_id', $this->user_id];
        $params[] = ['=', 'status', 1];
        $commonService = new CommonService();
        return $commonService->getAddressInfoByParams($params);
    }
    /**
     * 收货地址删除
     * @return array
     */
    public function actionDel() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));//收货地址唯一标识
        if($id<=0) {
            return BaseService::returnErrData([], 56000, "请求参数有误");
        }
        $commonService = new CommonService();
        return $commonService->delAddressInfoByParams($id, $this->user_id);
    }
    /**
     * 获取默认收货地址
     */
    public function actionGetDefaultInfo() {
        $order_id = intval(Yii::$app->request->post('order_id', 0));//订单id
        //获取默认收货地址
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $commonService = new CommonService();
        return $commonService->getDefaultAddress($order_id, $this->user_id);
    }
}
