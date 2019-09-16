<?php
/**
 * 支付相关接口请求入口操作
 * @文件名称: UserController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\pay\controllers;
use appcomponents\modules\common\CommonService;
use appcomponents\modules\order\models\OrderModel;
use appcomponents\modules\order\OrderService;
use appcomponents\modules\pay\PayService;
use source\controllers\UserBaseController;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;

class PayController extends UserBaseController
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
     * 购物车提交结算
     * @return array
     */
    public function actionPayBalance() {
        DmpLog::debug(Yii::$app->request->post());
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $orderIdArr = Yii::$app->request->post('order_id', "");
        $pay_type = intval(Yii::$app->request->post('pay_type', 2));
        $pay_from = intval(Yii::$app->request->post('pay_from', 1));
        $address_id = intval(Yii::$app->request->post('address_id', 0));
        if($address_id<=0) {
            return BaseService::returnErrData([], 54300, "请提交您的收货地址");
        }
        if(empty($orderIdArr)) {
            return BaseService::returnErrData([], 59000,"请求参数异常");
        }
        if(!is_array($orderIdArr)) {
            $orderIdArr = json_decode($orderIdArr,true);
        }
        $addressParams[] = ['=', 'user_id', $this->user_id];
        $addressParams[] = ['=', 'status', 1];
        $addressParams[] = ['=', 'id', $address_id];
        $commonService = new CommonService();
        $addressInfoRet = $commonService->getAddressInfoByParams($addressParams);
        if(!BaseService::checkRetIsOk($addressInfoRet)) {
            return BaseService::returnErrData([], 55200, "收货地址信息不对");
        }
        if(empty($orderIdArr) || !is_array($orderIdArr)) {
            return BaseService::returnErrData([], 53900, "参数异常");
        }
        $orderService = new OrderService();
        $dataArrRet = $orderService->getDataArr($this->user_id, $orderIdArr, [], 1, count($orderIdArr), ['id','title','status','user_id','total_amount']);
        $totalAmoutArr = [];
        $titleArr = [];
        if(BaseService::checkRetIsOk($dataArrRet)) {
            $dataArr = BaseService::getRetData($dataArrRet);
            $totalAmoutArr = isset($dataArr['totalAmountArr']) ? $dataArr['totalAmountArr'] : [];
            $titleArr= isset($dataArr['titleArr']) ? $dataArr['titleArr'] : [];
        } else {
            return $dataArrRet;
        }
        //修改收货地址
        $updateParams['id'] = $orderIdArr;
        $updateData['address_id'] = $address_id;
        $updateData['status'] = OrderModel::READY_PAY_STATUS;
        $updateData['pay_from'] = $pay_from;
        $updateData['pay_type'] = $pay_type;
        $ret = $orderService->updateAllByParams($updateParams, $updateData);
//        if(!BaseService::checkRetIsOk($ret)) {
//            return BaseService::returnErrData([], 58100, "当前订单提交的收货地址发生异常");
//        }
        $payService = new PayService();
        return $payService->addPayData($this->user_id, $orderIdArr, $totalAmoutArr,$titleArr, $address_id, $pay_type, $pay_from);
    }

    /**
     * 支付结算记录支付
     * @return array
     */
    public function actionPay() {
//        DmpLog::debug(Yii::$app->request->post());
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $pay_id = intval(Yii::$app->request->post('pay_id', 0));
        $payService = new PayService();
        $payParams[] = ['=', 'id', $pay_id];
        $payInfoRet = $payService->getPayInfoByByParams($payParams);
        $total_amount = 0;
        $my_trade_no = $payService->createMyTradeNo(25);
        $title = "";
        $order_ids = "";
        $pay_type = intval(Yii::$app->request->post('pay_type', 0));
        $payInfo = [];
        if(BaseService::checkRetIsOk($payInfoRet)) {
            $payInfo = BaseService::getRetData($payInfoRet);
            if(isset($payInfo['total_amounts']) && !empty($payInfo['total_amounts'])) {
                $totalAmount = explode(',', $payInfo['total_amounts']);
                foreach($totalAmount as $amout) {
                    $total_amount+=$amout;
                }
            }
            if(isset($payInfo['title']) && !empty($payInfo['title'])) {
                $title = $payInfo['title'];
            }
            if(isset($payInfo['pay_type']) && !empty($payInfo['pay_type'])) {
                $pay_type = $payInfo['pay_type'];
            }
            if(isset($payInfo['order_ids']) && !empty($payInfo['order_ids'])) {
                $order_ids = $payInfo['order_ids'];
            }
        } else {
            return BaseService::returnErrData([], 58100, "您请求的数据不存在，请重新发起支付");
        }
        $updatePayDataInfo['my_trade_no'] = $my_trade_no;
        $updateRet = $payService->updatePayInfoById($pay_id, $updatePayDataInfo);
        if(!BaseService::checkRetIsOk($updateRet)) {
            return BaseService::returnErrData([], 58100, "支付异常");
        }
        $payDataInfo['attach'] = $pay_id;//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $payDataInfo['body'] = $title ? $title : "商城在线支付";
        $payDataInfo['my_trade_no'] = $my_trade_no;
        $payDataInfo['total_amount'] = $total_amount*100;
        $payDataInfo['goods_tag'] = $order_ids;
        $url = "";
        if($pay_type) {
            if($pay_type ==2) {
                $url = $payService->WxMwebPay($payDataInfo);
                if(!empty($url)) {
                    return BaseService::returnOkData($url);
                }
                return BaseService::returnErrData($url, 514400, "微信支付异常");
            }
            if($pay_type ==1) {
                $url = $payService->AliMwebPay($payDataInfo);
                DmpLog::debug($url);
//                $url = 'https://openapi.alipay.com/gateway.do?alipay_sdk=alipay-sdk-php-20161101&app_id=2019031463539623&biz_content={"out_trade_no":"1553088366","product_code":"QUICK_WAP_WAY","total_amount":0.01,"subject":"煜雨测试手机网站支付","body":"煜雨手机网站","quit_url":"https://www.baidu.com/"}&charset=UTF-8&format=json&method=alipay.trade.wap.pay&notify_url=http://api.wbaole.com/pay/pay/ali-pay-notify-call-back&return_url=http://api.wbaole.com/pay/pay/ali-pay-notify-call-back&sign_type=RSA2&timestamp=2019-03-20 21:26:06&version=1.0&sign=j0nPjj1g5xR6Z1nZuI72GY5%2FG1%2B42iEuAW0DX5p5RO39tUIqghkhWQ5jSqt8boqK%2BX%2FaAz4G72H9jYI0hdZe%2B6m93aWqdjpYMu%2FhZT6cGNPueGnykieT424RdVF0pDv9aAj5bfuHqz55J%2FvCBUHJvlhWeymHFumsVbpveu5C%2FANLDIccz9bqj0%2BVwK334HJrDitTHjh0oBIZ80M%2BlobMDb6D%2BGgGgzJ2d2m2lOwizCaHrtFzKgeOfCeCnzxDjsu5U8dOZ%2BwHYMxxgj1Gg7OcPUl6JMNBSmZVYHNjadxKMmb0j47nunkd%2F8I4x1OqASowr6cuMgkesDL523qDrsnprg%3D%3D';
                if(!empty($url)) {
//                    $url = urlencode($url);
                    $ret = BaseService::returnOkData($url);
                    DmpLog::debug($ret);
                    return $ret;
                }
                return BaseService::returnErrData($url, 515500, "支付宝支付异常");
            }
        }
        return BaseService::returnErrData($url, 516000, "支付方式未知");
    }
    /**
     * 支付结算记录支付
     * @return array
     */
    public function actionGetPayResult()
    {
        if (!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $pay_id = intval(Yii::$app->request->post('pay_id', 0));
        if($pay_id<=0) {
            return BaseService::returnErrData([], 513300, "支付结果数据不存在");
        }
        $payService = new PayService();
        $parParams[] = ['=', 'id', $pay_id];
        return $payService->getPayInfoByByParams($parParams);
    }

    /**
     * 支付结算记录支付
     * @return array
     */
    public function actionGetResult()
    {
        //提交数据到购物车
        $pay_id = intval(Yii::$app->request->post('pay_id', 0));
        if($pay_id<=0) {
            return BaseService::returnErrData([], 513300, "支付结果数据不存在");
        }
        $payService = new PayService();
        $parParams[] = ['=', 'id', $pay_id];
        $parParams[] = ['=', 'status', 20];
        return $payService->getPayInfoByByParams($parParams);
    }
    /**
     * 微信支付回调
     */
    public function actionWxPayNotifyCallBack()
    {
        $payService = new PayService();
        $ret = $payService->WxPayNotifyCallBack();
        if ($ret) {
            return $ret;
        } else {
            DmpLog::debug("actionWxPayNotifyCallBack");
            DmpLog::debug($ret);
            return false;
        }
    }
    /**
     * 阿里支付回调
     */
    public function actionAliPayNotifyCallBack()
    {
        $payService = new PayService();
//        $ret = $payService->WxPayNotifyCallBack();
        DmpLog::debug("AliPayNotifyCallBack");
        DmpLog::debug(Yii::$app->request);
//        DmpLog::debug($ret);
////        return $ret;
//        if ($ret) {
//            DmpLog::debug($ret);
//            return $ret;
////            header("Location: ".Yii::$app->request->hostName."/paySuccess.html");
//        } else {
////            header("Location: ".Yii::$app->request->hostName."/paySuccess.html");
//            return false;
//        }
    }
    /**
     * 二维码支付
     * @return array
     */
    public function actionQrcodePay() {
        try {
//        DmpLog::debug(Yii::$app->request->post());
            if(!isset($this->user_id) || !$this->user_id) {
                return BaseService::returnErrData('', 5001, "登陆状态已失效");
            }
            //提交数据到购物车
            $pay_id = intval(Yii::$app->request->post('pay_id', 0));
            $pay_type = intval(Yii::$app->request->post('pay_type', 0));
            $payService = new PayService();
            $payParams[] = ['=', 'id', $pay_id];
            $payInfoRet = $payService->getPayInfoByByParams($payParams);
            $total_amount = 0;
            $my_trade_no = $payService->createMyTradeNo(25);
            $title = "";
            $order_ids = "";
            $payInfo = [];
            if(BaseService::checkRetIsOk($payInfoRet)) {
                $payInfo = BaseService::getRetData($payInfoRet);
                if(isset($payInfo['total_amounts']) && !empty($payInfo['total_amounts'])) {
                    $totalAmount = explode(',', $payInfo['total_amounts']);
                    foreach($totalAmount as $amout) {
                        $total_amount+=$amout;
                    }
                }
                if(isset($payInfo['my_trade_no']) && !empty($payInfo['my_trade_no'])) {
                    $my_trade_no = $payInfo['my_trade_no'];
                }
                if(isset($payInfo['title']) && !empty($payInfo['title'])) {
                    $title = $payInfo['title'];
                }
                if(isset($payInfo['pay_type']) && !empty($payInfo['pay_type'])) {
                    $pay_type = $payInfo['pay_type'];
                }
                if(isset($payInfo['order_ids']) && !empty($payInfo['order_ids'])) {
                    $order_ids = $payInfo['order_ids'];
                }
            } else {
                return BaseService::returnErrData([], 527400, "您请求的数据不存在，请重新发起支付");
            }
            //商户订单号
            $payDataInfo = [];
            $payDataInfo['my_trade_no'] = $my_trade_no ? $my_trade_no : $payService->createMyTradeNo(25);
            $payDataInfo['order_ids'] = $order_ids;
            $payDataInfo['goods_tag'] = $order_ids;
            $payDataInfo['body'] = $title ? $title : "商城在线支付";
            $payDataInfo['attach'] = $pay_id;//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $payDataInfo['total_amount'] = $total_amount;
            if($pay_type ==2) {
                return $payService->WxQrcodePay($payDataInfo);
            }
            if($pay_type ==1) {
                return $payService->AliQrcodePay($payDataInfo);
            }
            return BaseService::returnErrData([], 530200, "支付方式未知");
        } catch (BaseException $e) {
            return BaseService::returnErrData([], 533900, "支付请求");
        }
    }
    /**
     * 通过支付id获取订单的列表数据
     * @return array
     */
    public function actionGetOrders() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $pay_id = intval(Yii::$app->request->post('pay_id', 0));
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', -1));
        if(empty($pay_id)) {
            return BaseService::returnErrData([], 531100, "请求参数异常");
        }
        $payService = new PayService();
        $payParams[] = ['=', 'id', $pay_id];
        $payInfoRet = $payService->getPayInfoByByParams($payParams);
        if(BaseService::checkRetIsOk($payInfoRet)) {
            $payInfo = BaseService::getRetData($payInfoRet);
            if(isset($payInfo['order_ids']) && !empty($payInfo['order_ids'])) {
                $order_ids = $payInfo['order_ids'];
                if(!empty($order_ids)) {
                    $orderIdArr = explode(',', $order_ids);
                    $orderService = new OrderService();
                    $params[] = ['in', 'id', $orderIdArr];
                    $params[] = ['=', 'user_id', $this->user_id];
                    return $orderService->getList($params, [], $page, $size, ['*']);
                }
            }
            $data = [
                'dataList' => [],
                'count' => 0,
            ];
            return BaseService::returnOkData($data);
        }
        return $payInfoRet;
    }
}
