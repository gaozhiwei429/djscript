<?php

namespace appcomponents\modules\common\controllers;

use appcomponents\modules\common\CommonService;
use source\controllers\BaseController;
use Yii;
/**
 * 省市区数据接口
 * @package appcomponents\modules\common\controllers
 */
class AddrController extends BaseController
{
    /**
     * 获取省份数据接口
     * @return array
     */
    public function actionGetProvinceList() {
        $p = intval(Yii::$app->request->post('p'));//当前第几页
        $size = intval(Yii::$app->request->post('size', 100));//每页显示多少条
        $name = trim(Yii::$app->request->post('name', null));//模糊查询名称
        $status = intval(Yii::$app->request->post('status', 0));//状态【1已开启，0未开启】
        $params[] = ['status'=>$status];
//        $params[] = ['status'=>AddrProvinceModel::IS_STATUS];
        if(!empty($name)) {
            $params[] = ['like', 'name', $name];
        }
        $commonService = new CommonService();
        return $commonService->getProvinceList($params, [], $p, $size);
    }
    /**
     * 获取市数据接口
     * @return array
     */
    public function actionGetCityList() {
        $p = intval(Yii::$app->request->post('p', 1));//当前第几页
        $size = intval(Yii::$app->request->post('size', 100));//每页显示多少条
        $province_id = intval(Yii::$app->request->post('province_id', null));//省份id
        $name = trim(Yii::$app->request->post('name', null));//模糊查询名称
        $status = intval(Yii::$app->request->post('status', 0));//状态【1已开启，0未开启】
        $params[] = ['status'=>$status];
//        $params[] = ['status'=>AddrCityModel::IS_STATUS];
        if(!empty($name)) {
            $params[] = ['like', 'name', $name];
        }
        if(!empty($province_id)) {
            $params[] = ['=', 'province_id', $province_id];
        }
        $commonService = new CommonService();
        return $commonService->getCityList($params, [], $p, $size);
    }
    /**
     * 获取区县数据接口
     * @return array
     */
    public function actionGetAreaList() {
        $p = intval(Yii::$app->request->post('p', 1));//当前第几页
        $size = intval(Yii::$app->request->post('size', 100));//每页显示多少条
        $city_id = intval(Yii::$app->request->post('city_id', null));//所属城市id
        $name = trim(Yii::$app->request->post('name', null));//模糊查询名称
        $status = intval(Yii::$app->request->post('status', 0));//状态【1已开启，0未开启】
        $params[] = ['status'=>$status];
        if(!empty($name)) {
            $params[] = ['like', 'name', $name];
        }
        if(!empty($city_id)) {
            $params[] = ['=', 'city_id', $city_id];
        }
        $commonService = new CommonService();
        return $commonService->getAreaList($params, [], $p, $size);
    }
    public function actionTree() {
        $commonService = new CommonService();
        return $commonService->addrTree();
    }
}
