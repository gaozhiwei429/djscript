<?php
/**
 * 订单相关接口请求入口操作
 * @文件名称: OrderController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\order\controllers;
use appcomponents\modules\common\CommonService;
use appcomponents\modules\order\OrderService;
use appcomponents\modules\order\RefundService;
use appcomponents\modules\pay\PayService;
use appcomponents\modules\project\ProjectService;
use source\controllers\UserBaseController;
use source\libs\DmpLog;
use source\manager\BaseService;
use Yii;

class OrderController extends UserBaseController
{
    /**
     * 用户登录态基础类验证
     * @return array
     */
    public function beforeAction($action){
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }

    /**
     * 用户加入产品到购物车中
     * @return array
     */
    public function actionAddGoodsCar() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $project_id = intval(Yii::$app->request->post('project_id', 0));
        $buy_number = intval(Yii::$app->request->post('buy_number', 0));
        $pay_from = intval(Yii::$app->request->post('pay_from', 1));//支付来源如【1APP，2Web，3微信公众号，4线下】
        $pay_type = intval(Yii::$app->request->post('pay_type', 2));//支付方式【1支付宝，2微信，3银联】
        $type = intval(Yii::$app->request->post('type', 1));
        $renewal_id = intval(Yii::$app->request->post('renewal_id', 0));
        if(($project_id<=0 && $renewal_id<=0) || $pay_from<=0 || $type<=0) {
            return BaseService::returnErrData([], 52900, "请求参数异常");
        }
        if($project_id > 0 && $buy_number <= 0) {
            return BaseService::returnErrData([], 53900, "购买数量填写错误");
        }
        $orderService = new OrderService();
        return $orderService->addGoodsCar($this->user_id, $project_id, $buy_number, $pay_from, $pay_type, $type, $renewal_id);
    }

    /**
     * 批量删除购物车
     * @return array
     */
    public function actionDel() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $idArr = Yii::$app->request->post('id', []);
        if(empty($idArr)) {
            return BaseService::returnErrData([], 59000,"请求参数异常");
        }
        if(!is_array($idArr)) {
            $idArr = json_decode($idArr,true);
        }
        $orderService = new OrderService();
        return $orderService->delData($this->user_id, $idArr);
    }
    /**
     * 修改购物车数量
     * @return array
     */
    public function actionEditBuyNumber() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $buy_number = intval(Yii::$app->request->post('buy_number', 1));
        if($id>0) {
            $orderService = new OrderService();
            return $orderService->editBuyNumber($this->user_id, $id, $buy_number);
        }
        return BaseService::returnErrData([], 582000, "请求参数异常");
    }
    /**
     * 取消订单
     * @return array
     */
    public function actionCancel() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $content = trim(Yii::$app->request->post('content', "用户主动申请取消订单"));
        $describe = trim(Yii::$app->request->post('describe', ""));
        $picUrlArr = Yii::$app->request->post('pic_url', []);
        if(!is_array($picUrlArr)) {
            $picUrlArr = json_decode($picUrlArr,true);
        }

        if($id>0) {
            $orderService = new OrderService();
            return $orderService->cancel($this->user_id, $id, $content, $describe, $picUrlArr);
        }
        return BaseService::returnErrData([], 59700, "请求参数异常");
    }
    /**
     * 获取订单详情数据
     * @return array
     */
    public function actionGetInfo() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        if($id>0) {
            $orderService = new OrderService();
            $params[] = ['=', 'id', $id];
            $params[] = ['=', 'user_id', $this->user_id];
            $orderInfoRet = $orderService->getOrderInfoByParams($params);
            $addressInfo = [];
            $address_id = 0;
            $projectInfo = [];
            if(BaseService::checkRetIsOk($orderInfoRet)) {
                $orderInfo = BaseService::getRetData($orderInfoRet);
                if(isset($orderInfo['project_id']) && !empty($orderInfo['project_id'])) {
                    $projectService = new ProjectService();
                    $projectParams[] = ['=', 'id', $orderInfo['project_id']];
                    $projectRet = $projectService->getInfo($projectParams);
                    if(BaseService::checkRetIsOk($projectRet)) {
                        $projectInfo = BaseService::getRetData($projectRet);
                    }
                }
                if(isset($orderInfo['address_id']) && !empty($orderInfo['address_id'])) {
                    $address_id = isset($orderInfo['address_id']) ? $orderInfo['address_id'] : 0;
                } else {
                    $payService = new PayService();
                    $payParams[] = ['=', 'user_id', $this->user_id];
                    $payParams[] = ['=', 'order_id',$id];
                    $payInfoRet = $payService->getDetailInfo($payParams);
                    if(BaseService::checkRetIsOk($payInfoRet)) {
                        $payInfo = BaseService::getRetData($payInfoRet);
                        $address_id = isset($payInfo['address_id']) ? $payInfo['address_id'] : 0;
                    }
                }
                if($address_id) {
                    $addressParams[] = ['=', 'user_id', $this->user_id];
//                    $addressParams[] = ['=', 'status', 1];
                    $addressParams[] = ['=', 'id', $address_id];
                    $commonService = new CommonService();
                    $addressInfoRet = $commonService->getAddressInfoByParams($addressParams);
                    $addressInfo = BaseService::getRetData($addressInfoRet);
                }
                $refundService = new RefundService();
                $refundParams[] = ['=', 'order_id', $id];
                $refundInfoRet = $refundService->getInfoByParams($refundParams);
                $orderInfo['addressInfo'] = $addressInfo;
                $orderInfo['projectInfo'] = $projectInfo;
                $orderInfo['refundInfo'] = BaseService::getRetData($refundInfoRet);
                return BaseService::returnOkData($orderInfo);
            }
        }
        return BaseService::returnErrData([], 511500, "请求参数异常");
    }
    /**
     * 确认签收订单
     * @return array
     */
    public function actionConfirmReceipt() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        if($id>0) {
            $orderService = new OrderService();
            return $orderService->confirmReceipt($this->user_id, $id);
        }
        return BaseService::returnErrData([], 511500, "请求参数异常");
    }
    /**
     * 提醒发货
     * @return array
     */
    public function actionRemindSend() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData([], 5001, "当前账号登陆异常");
        }
        $id = intval(Yii::$app->request->post('id', 0));
        $is_emind_send = intval(Yii::$app->request->post('is_emind_send', 1));
        if($id>0) {
            $orderService = new OrderService();
            return $orderService->remindSend($this->user_id, $id, $is_emind_send);
        }
        return BaseService::returnErrData([], 582000, "请求参数异常");
    }
}
