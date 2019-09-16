<?php
/**
 * 公用service
 * @文件名称: CommonService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\AddressModel;
use appcomponents\modules\pay\PayService;
use source\libs\DmpRedis;
use source\libs\Upload;
use appcomponents\modules\common\models\AddrAreaModel;
use appcomponents\modules\common\models\AddrCityModel;
use appcomponents\modules\common\models\AddrProvinceModel;
use appcomponents\modules\common\models\FilesModel;
use source\libs\Common;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class CommonService extends BaseService
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
     * 腾讯cos文件上传
     * @param $user_id
     * @param $files
     * @return array
     */
    public function uploadTencentCos($user_id, $local_path, $key) {
        $images = [];
        if(isset($local_path) && !empty($key)) {
            $UploadObj = new Upload();
            $uploadFiles = $UploadObj->uploadTencentCos($local_path, $key);
            var_dump($uploadFiles);die;
            if(BaseService::checkRetIsOk($uploadFiles)) {
                $images = BaseService::getRetData($uploadFiles);
            }
            return $uploadFiles;
        }
        return BaseService::returnErrData($images, 500, '上传文件不能为空');
    }

    /**
     * 阿里oss文件上传
     * @param $user_id
     * @param $files
     * @return array
     */
    public function uploadAlioss($user_id, $local_path, $key) {
        $images = [];
        if(isset($local_path) && !empty($key)) {
            $UploadObj = new Upload();
            $uploadFiles = $UploadObj->uploadAliCos($local_path, $key);
            if(BaseService::checkRetIsOk($uploadFiles)) {
                $images = BaseService::getRetData($uploadFiles);
            }
            return $uploadFiles;
        }
        return BaseService::returnErrData($images, 500, '上传文件不能为空');
    }
    /**
     * 图片文件上传类
     * @param $user_id
     * @param $files
     * @return array
     */
    public function uploadImg($user_id, $files, $isQiniu=true) {
        $images = [];
        if(isset($files['files']) && !empty($files['files'])) {
            $UploadObj = new Upload();
            $uploadFiles = $UploadObj->uploadImg($files);
            if(BaseService::checkRetIsOk($uploadFiles)) {
                $images = BaseService::getRetData($uploadFiles);
                if($isQiniu) {
                    if(!empty($images)) {
                        $this->addFiles($user_id, $images);
                        $files = [];
                        if(is_array($images)) {
                            foreach($images as $file) {
                                $qiniuRet = $UploadObj->uploadQiNiu($file);
                                if(BaseService::checkRetIsOk($qiniuRet)) {
                                    $ret = BaseService::getRetData($qiniuRet);
                                    $files[] = isset($ret['key']) ? $ret['key'] : '';
                                }
                            }
                        } else {
                            $qiniuRet = $UploadObj->uploadQiNiu($images);
                            if(BaseService::checkRetIsOk($qiniuRet)) {
                                $ret = BaseService::getRetData($qiniuRet);
                                $files[] = isset($ret['key']) ? $ret['key'] : '';
                            }
                        }
                        return BaseService::returnOkData($files);
                    }
                }
            }
            return $uploadFiles;
        }
        return BaseService::returnErrData($images, 500, '上传文件不能为空');
    }
    /**
     * 添加文件到存储库
     * @param $user_id
     * @param $files
     * @return array
     */
    public function addFiles($user_id, $files) {
        try {
            $fileModel = new FilesModel();
            $ret = $fileModel->addAll($user_id, $files);
            if($ret) {
                return BaseService::returnOkData($ret);
            }
            return BaseService::returnErrData($ret, 500, '文件数据添加失败');
        } catch(BaseException $e) {
            return BaseService::returnErrData($e, 500, '失败');
        }
    }
    /**
     * 省份数据获取
     * @return array
     */
    public function getProvinceList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $provinceModel = new AddrProvinceModel();
        $provinceList = $provinceModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
        if(!empty($provinceList)) {
            return BaseService::returnOkData($provinceList);
        }
        return BaseService::returnErrData([], 500, "省份数据获取异常");
    }
    /**
     * 省份数据详情获取
     * @return array
     */
    public function getProvinceInfo($params) {
        if(empty($params) || !is_array($params)) {
            return BaseService::returnErrData([], 516000, "请求参数异常");
        }
        $provinceModel = new AddrProvinceModel();
        $provinceInfo = $provinceModel->getInfoByParams($params);
        if(!empty($provinceInfo)) {
            return BaseService::returnOkData($provinceInfo);
        }
        return BaseService::returnErrData([], 500, "省份数据获取异常");
    }
    /**
     * 所属城市数据获取
     * @return array
     */
    public function getCityList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $addrCityModel = new AddrCityModel();
        $cityList = $addrCityModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "城市数据获取异常");
    }
    /**
     * 城市数据详情获取
     * @return array
     */
    public function getCityInfo($params) {
        if(empty($params) || !is_array($params)) {
            return BaseService::returnErrData([], 516000, "请求参数异常");
        }
        $cityModel = new AddrCityModel();
        $cityInfo = $cityModel->getInfoByParams($params);
        if(!empty($cityInfo)) {
            return BaseService::returnOkData($cityInfo);
        }
        return BaseService::returnErrData([], 500, "城市数据获取异常");
    }
    /**
     * 所属城市数据获取
     * @return array
     */
    public function getAreaList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $addrCityModel = new AddrAreaModel();
        $cityList = $addrCityModel->getListData($params, $orderBy, $offset, $limit, $fied, $index);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "城市数据获取异常");
    }
    /**
     * 城市数据详情获取
     * @return array
     */
    public function getAreaInfo($params) {
        if(empty($params) || !is_array($params)) {
            return BaseService::returnErrData([], 516000, "请求参数异常");
        }
        $areaModel = new AddrAreaModel();
        $areaInfo = $areaModel->getInfoByParams($params);
        if(!empty($areaInfo)) {
            return BaseService::returnOkData($areaInfo);
        }
        return BaseService::returnErrData([], 500, "区县数据获取异常");
    }
    /**
     * 添加收货地址接口
     * @param $addressData
     * @return array
     */
    public function createAddress($addressData) {
        if(!empty($addressData)) {
            $addressModel = new AddressModel();
            $insert = $addressModel->addInfo($addressData);
            if($insert) {
                return BaseService::returnOkData($insert);
            }
        }
        return BaseService::returnErrData([], 530000, "添加收货地址异常");
    }
    /**
     * 编辑收货地址接口
     * @param $addressData
     * @return array
     */
    public function editAddress($addressData) {
        $addressModel = new AddressModel();
        if(!empty($addressData)) {
            $update = false;
            if(isset($addressData['id']) && $addressData['id']){
                $update = $addressModel->updateInfo($addressData['id'], $addressData);
            } else {
                $update = $addressModel->addInfo($addressData);
            }
            if($update) {
                return BaseService::returnOkData($update);
            }
        }
        return BaseService::returnErrData([], 530000, "收货地址处理异常");
    }
    /**
     * 获取收货地址列表接口
     * @param array $params
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @return array
     */
    public function getAddressList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $index=false) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        if(!empty($params)) {
            $addressModel = new AddressModel();
            $addressList = $addressModel->getListData($params, $orderBy, $offset, $limit, $fied);
            if(isset($addressList['dataList']) && !empty($addressList['dataList'])) {
                if(!$index) {
                    return BaseService::returnOkData($addressList);
                }
                $addressProvinceIdArr = [];
                $addressCityIdArr = [];
                $addressAreaIdArr = [];
                foreach($addressList['dataList'] as $addressInfo) {
                    if(isset($addressInfo['province_id'])) {
                        $addressProvinceIdArr[] = $addressInfo['province_id'];
                    }
                    if(isset($addressInfo['city_id'])) {
                        $addressCityIdArr[] = $addressInfo['city_id'];
                    }
                    if(isset($addressInfo['area_id'])) {
                        $addressAreaIdArr[] = $addressInfo['area_id'];
                    }
                }
                $provinceDataList = [];
                if(!empty($addressProvinceIdArr)) {
                    $provinceParams[] = ['in', 'id', $addressProvinceIdArr];
                    $provinceDataListRet = $this->getProvinceList($provinceParams, [], 1, -1, ['id','name'], $index);
                    $provinceList = BaseService::getRetData($provinceDataListRet);
                    $provinceDataList = isset($provinceList['dataList']) ? $provinceList['dataList'] : [];
                }
                $cityDataList = [];
                if(!empty($addressCityIdArr)) {
                    $cityParams[] = ['in', 'id', $addressCityIdArr];
                    $cityDataListRet = $this->getCityList($cityParams, [], 1, -1, ['id','name'], $index);
                    $cityList = BaseService::getRetData($cityDataListRet);
                    $cityDataList = isset($cityList['dataList']) ? $cityList['dataList'] : [];
                }
                $areaDataList = [];
                if(!empty($addressAreaIdArr)) {
                    $areaParams[] = ['in', 'id', $addressAreaIdArr];
                    $areaDataListRet = $this->getAreaList($areaParams, [], 1, -1, ['id','name'], $index);
                    $areaList = BaseService::getRetData($areaDataListRet);
                    $areaDataList = isset($areaList['dataList']) ? $areaList['dataList'] : [];
                }
                foreach($addressList['dataList'] as &$addressInfo) {
                    $addressInfo['province_name'] = "";
                    $addressInfo['area_name'] = "";
                    $addressInfo['city_name'] = "";
                    if(isset($provinceDataList[$addressInfo['province_id']])) {
                        $addressInfo['province_name'] = $provinceDataList[$addressInfo['province_id']]['name'];
                    }
                    if(isset($areaDataList[$addressInfo['area_id']])) {
                        $addressInfo['area_name'] = $areaDataList[$addressInfo['area_id']]['name'];
                    }
                    if(isset($cityDataList[$addressInfo['city_id']])) {
                        $addressInfo['city_name'] = $cityDataList[$addressInfo['city_id']]['name'];
                    }
                }
            }
            return BaseService::returnOkData($addressList);
        }
        return BaseService::returnOkData([]);
    }

    /**
     * 批量更新收货地址
     * @param $params
     * @param $where
     * @return array
     */
    public function updateAllDataList($where, $updateData) {
        if((empty($updateData) || !is_array($updateData)) || (empty($where) || !is_array($where))) {
            return BaseService::returnErrData([], 534400, "收货地址数据更新失败");
        }
        $addressModel = new AddressModel();
        $updateAddress = $addressModel->updateAllDataList($where, $updateData);
        if(!$updateAddress) {
            return BaseService::returnErrData($updateAddress, 534900, "收货地址修改失败");
        }
        return BaseService::returnOkData($updateAddress);
    }
    /**
     * 批量更新收货地址
     * @param $params
     * @param $where
     * @return array
     */
    public function getUpdateAllAddress($where, $updateData) {
        if((empty($updateData) || !is_array($updateData)) || (empty($where) || !is_array($where))) {
            return BaseService::returnErrData([], 536100, "收货地址数据更新失败");
        }
        $addressModel = new AddressModel();
        $updateAddress = $addressModel->updateAllDataList($where, $updateData);
        if(!$updateAddress) {
            return BaseService::returnErrData($updateAddress, 536600, "收货地址修改失败");
        }
        return BaseService::returnOkData($updateAddress);
    }
    /**
     * 获取收货地址详情
     * @param $params
     * @return array
     */
    public function getAddressInfoByParams($params) {
        if(empty($params) || !is_array($params)) {
            return BaseService::returnErrData([], 537700, "请求参数异常");
        }
        $addressModel = new AddressModel();
        $addressInfo = $addressModel->getInfoByParams($params);
        if(!empty($addressInfo)) {
            $addressInfo['province_name'] = "";
            $addressInfo['area_name'] = "";
            $addressInfo['city_name'] = "";
            if(isset($addressInfo['province_id']) && $addressInfo['province_id']) {
                $provinceParams[] = ['=', 'id', $addressInfo['province_id']];
                $provinceDataRet = $this->getProvinceInfo($provinceParams);
                $provinceInfo = BaseService::getRetData($provinceDataRet);
                $addressInfo['province_name'] = isset($provinceInfo['name']) ? $provinceInfo['name'] : "";
            }
            if(isset($addressInfo['city_id']) && $addressInfo['city_id']) {
                $cityParams[] = ['=', 'id', $addressInfo['city_id']];
                $cityDataRet = $this->getCityInfo($cityParams);
                $cityData = BaseService::getRetData($cityDataRet);
                $addressInfo['city_name'] = isset($cityData['name']) ? $cityData['name'] : "";
            }
            if(isset($addressInfo['area_id']) && $addressInfo['area_id']) {
                $areaParams[] = ['=', 'id', $addressInfo['area_id']];
                $areaDataRet = $this->getAreaInfo($areaParams);
                $areaData = BaseService::getRetData($areaDataRet);
                $addressInfo['area_name'] = isset($areaData['name']) ? $areaData['name'] : "";
            }
            return BaseService::returnOkData($addressInfo);
        }
        return BaseService::returnErrData([], 538500, "当前数据不存在");
    }
    /**
     * 删除收货地址详情
     * @param $params
     * @return array
     */
    public function delAddressInfoByParams($id, $user_id) {
        if($id<=0 || $user_id<=0) {
            return BaseService::returnErrData([], 537700, "请求参数异常");
        }
        $params['user_id'] = $user_id;
        $params['status'] = 1;
        $params['id'] = $id;
        $addressInfoRet = $this->getAddressInfoByParams($params);
        if(BaseService::checkRetIsOk($addressInfoRet)) {
            return $this->updateAddressInfo($params, ['status'=>0]);
        }
        return BaseService::returnErrData([], 538600, "请求数据异常");
    }
    public function updateAddressInfo($params, $updateData) {
        $addressModel = new AddressModel();
        $addressUpdate = $addressModel->updateAllDataList($params, $updateData);
        if($addressUpdate) {
            return BaseService::returnOkData($addressUpdate);
        }
        return BaseService::returnErrData($addressUpdate,542500, "更新失败");
    }
    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边)
     * 加入订单详情id到队列
     * @param $id
     * @return array
     */
    public function redisRpush($key, $idArr) {
        $dmpRedis = new DmpRedis();
        //子订单详情记录key
        $lpush = $dmpRedis->RpushRedis($key, $idArr);
        if($lpush) {
            return BaseService::returnOkData($lpush);
        }
        return BaseService::returnErrData($lpush, 500, "redis加入队列异常");
    }
    /**
     * 获取省市区的联动数据接口
     * @return array
     */
    public function addrTree() {
        $params[] = ['=', 'status', 1];
        $orderBy = ['order_id'=>SORT_DESC];
        $provinceListRet = $this->getProvinceList($params, $orderBy, 1, -1,['id','name']);
        $provinceIndexList = [];
        $cityIndexList = [];
        $areaIndexList = [];
        if(BaseService::checkRetIsOk($provinceListRet)) {
            $provinceDataList = BaseService::getRetData($provinceListRet);
            $provinceList = isset($provinceDataList['dataList']) ? $provinceDataList['dataList'] : [];
            if(!empty($provinceList)) {
                foreach($provinceList as $provinceInfo) {
                    $provinceIndexList[] = $provinceInfo;
                }
            }
            $cityListRet = $this->getCityList($params, $orderBy, 1, -1,['id','name','province_id']);
            if(BaseService::checkRetIsOk($cityListRet)) {
                $cityDataList = BaseService::getRetData($cityListRet);
                $cityList = isset($cityDataList['dataList']) ? $cityDataList['dataList'] : [];
                if(!empty($cityList)) {
                    foreach($cityList as $cityInfo) {
                        $cityIndexList[$cityInfo['province_id']][] = $cityInfo;
                    }
                }
                $areaListRet = $this->getAreaList($params, $orderBy, 1, -1,['id','name','city_id']);
                if(BaseService::checkRetIsOk($areaListRet)) {
                    $areaDataList = BaseService::getRetData($areaListRet);
                    $areaList = isset($areaDataList['dataList']) ? $areaDataList['dataList'] : [];
                    if(!empty($areaList)) {
                        foreach($areaList as $areaInfo) {
                            $areaIndexList[$areaInfo['city_id']][] = $areaInfo;
                        }
                    }
                }
                foreach($cityIndexList as $province_id=>&$cityInfoList) {
                    foreach($cityInfoList as &$cityInfo) {
                        if(isset($cityInfo['id']) && isset($areaIndexList[$cityInfo['id']])) {
                            $cityInfo['area'] = $areaIndexList[$cityInfo['id']];
                        } else {
                            $cityInfo['area'] = [];
                        }
                    }
                }
            }
        }
        if(!empty($provinceIndexList)) {
            foreach($provinceIndexList as &$provinceIndexInfo) {
                if(isset($provinceIndexInfo['id']) && isset($cityIndexList[$provinceIndexInfo['id']])) {
                    $provinceIndexInfo['city'] = $cityIndexList[$provinceIndexInfo['id']];
                } else {
                    $provinceIndexInfo['city'] = [];
                }
            }
            return BaseService::returnOkData($provinceIndexList);
        }
        return BaseService::returnErrData($provinceIndexList, 534700, "请求数据不存在");
    }
    /**
     * 获取默认收货地址详情数据
     * @param $user_id
     * @param int $order_id
     * @return array
     */
    public function getDefaultAddress($order_id, $user_id) {
        if($order_id) {
            //获取最新的订单id对应的
            $payService = new PayService();
            $parDetailParams[] = ['=', 'order_id', $order_id];
            $payInfoRet = $payService->getPayDetailInfoByParams($parDetailParams);
            if(BaseService::checkRetIsOk($payInfoRet)) {
                $payInfo = BaseService::getRetData($payInfoRet);
                $address_id = isset($payInfo['address_id']) ? $payInfo['address_id'] : 0;
                if($address_id) {
                    $addressInfoParams[] = ['=', 'id', $address_id];
                    $commonService = new CommonService();
                    return $commonService->getAddressInfoByParams($addressInfoParams);
                }
            }
        }
        $params[] = ['=', 'is_default', 1];
        $params[] = ['=', 'status', 1];
        $params[] = ['=', 'user_id', $user_id];
        $commonService = new CommonService();
        $defaultInfoRet = $commonService->getAddressInfoByParams($params);
        if(BaseService::checkRetIsOk($defaultInfoRet)) {
            return $defaultInfoRet;
        }
        $addressParams[] = ['=', 'user_id', $user_id];
        $commonService = new CommonService();
        return $commonService->getAddressInfoByParams($addressParams);
    }
}
