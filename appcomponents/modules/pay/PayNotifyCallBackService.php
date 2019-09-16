<?php
/**
 * 整合支付回调相关的类
 * 流程：
 * 1、调用统一下单，取得mweb_url，通过mweb_url调起微信支付中间页
 * 2、用户在微信支付收银台完成支付或取消支付
 * 3、支付完成之后，微信服务器会通知支付成功
 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 * pay module definition class
 */
namespace appcomponents\modules\pay;
use appcomponents\modules\common\CommonService;
use appcomponents\modules\my\models\UserProjectModel;
use appcomponents\modules\my\UserProjectService;
use appcomponents\modules\order\models\OrderModel;
use appcomponents\modules\order\OrderService;
use appcomponents\modules\pay\models\PayModel;
use source\libs\DmpLog;
use source\libs\WxPay\lib\WxPayApi;
use source\libs\WxPay\lib\WxPayConfig;
use source\libs\WxPay\lib\WxPayNotify;
use source\libs\WxPay\lib\WxPayOrderQuery;
use source\manager\BaseService;
use \Yii;


class PayNotifyCallBackService extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
//        DmpLog::debug("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
    //重写回调处理函数
    public function NotifyProcess($data, &$msg) {
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        if(!array_key_exists("out_trade_no", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        DmpLog::debug("NotifyProcess");
        DmpLog::debug($data);
        $pay_id = $data['attach'];//主支付单id
        $payService = new PayService();
        $orderService = new OrderService();
        $transaction_id = $data["transaction_id"];//获取微信的订单号
        $my_trade_no = $data["out_trade_no"];//获取商户系统内部的订单号
        $payParams[] = ['=', 'id', $pay_id];
        $payInfoRet = $payService->getPayInfoByByParams($payParams);
        $payDataInfo = [];
        if(!BaseService::checkRetIsOk($payInfoRet)) {
            $payDataInfo['id'] = $pay_id;
            $payDataInfo['my_trade_no'] = $my_trade_no;
            $payDataInfo['receipt_amount'] = isset($data["cash_fee"]) ? floatval($data["cash_fee"]/100) : 0;//微信支付金额数据表存储
            $payDataInfo['pay_type'] = 2;//支付方式【1支付宝，2微信】
            $payDataInfo['app_id'] = WxPayConfig::APPID;//支平台的商户号
            $payDataInfo['title'] = '商城产品购买';//设置商品或支付单简要描述
            $payDataInfo['goods_tag'] = isset($payDataInfo['title']) ? $payDataInfo['title'] : "";//设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
            $payDataInfo['attach'] = $pay_id;//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            //order_detail_map关联的类型【1sku订单，2套餐卡订单, 3违约金】
            $payDataInfo['total_amounts'] = isset($data["total_fee"]) ? floatval($data["total_fee"]/100) : 0;//微信支付金额
            $payDataInfo['coupon_amount'] = isset($data["coupon_fee"]) ? floatval($data["coupon_fee"]/100) : 0;//代金券金额
            $payDataInfo['coupon_count'] = isset($data["coupon_count"]) ? intval($data["coupon_count"]) : 0;//代金券使用数量
            $payDataInfo['coupon_ids'] = "";//代金券ID
            $payDataInfo['coupon_fees'] = "";//单个代金券支付金额
            if(isset($data['coupon_count']) &&  $data['coupon_count']> 0) {
                $coupon_ids = [];
                $coupon_fees = [];
                for($i=0; $i<$payDataInfo['coupon_count']; $i++) {
                    if(isset($payDataInfo["coupon_id_$i"])) {
                        $coupon_ids[] = isset($payDataInfo["coupon_id_$i"]) ? $payDataInfo["coupon_id_$i"] : 0;
                        $coupon_fees[] = isset($payDataInfo["coupon_fee_$i"]) ? floatval($payDataInfo["coupon_fee_$i"]/100) : 0;
                    }
                }
                $payDataInfo['coupon_ids'] = implode(',', $coupon_ids);
                $payDataInfo['coupon_fees'] = implode(',', $coupon_fees);
            }
            $payRet = $payService->createPayData($payDataInfo);
            $payId = BaseService::getRetData($payRet);
            $payInfoParams[] = ['=', 'id', $payId];
            $payInfoRet = $payService->getPayInfoByByParams($payInfoParams);
        }
        $payInfo = BaseService::getRetData($payInfoRet);
        $payId = isset($payInfo['id']) ? $payInfo['id'] : 0;
        $order_ids = isset($payInfo['order_ids']) ? $payInfo['order_ids'] : "";
        $update['receipt_amount'] = floatval($data["cash_fee"]/100);//微信支付金额数据表存储
        $update['buyer_id'] = $data["openid"];
        $update['trade_no'] = $transaction_id;
        $update['status'] = PayModel::PAY_STATUS;
        $update['pay_type'] = 2;//支付方式【1支付宝，2微信】
        $update['callbak_result'] = json_encode($data);
        $call_back_time = isset($data['time_end']) ? date('Y-m-d H:i:s', strtotime($data['time_end'])) : date('Y-m-d H:i:s');

        $update['my_trade_no'] = $my_trade_no;
        $update['total_fee'] = floatval($data["total_fee"]/100);//微信支付金额
        $update['callbak_time'] = $call_back_time;

        $update['coupon_amount'] = isset($data["coupon_fee"]) ? floatval($data["coupon_fee"]/100) : 0;//代金券金额
        $update['coupon_count'] = isset($data["coupon_count"]) ? intval($data["coupon_count"]) : 0;//代金券使用数量
        $update['coupon_ids'] = 0;//代金券ID
        $update['coupon_fees'] = 0;//单个代金券支付金额
        if($update['coupon_count'] > 0) {
            $coupon_ids = [];
            $coupon_fees = [];
            for($i=0; $i<$update['coupon_count']; $i++) {
                if(isset($data["coupon_id_$i"])) {
                    $coupon_ids[] = isset($data["coupon_id_$i"]) ? $data["coupon_id_$i"] : 0;
                    $coupon_fees[] = isset($data["coupon_fee_$i"]) ? floatval($data["coupon_fee_$i"]/100) : 0;
                }
            }
            $update['coupon_ids'] = implode(',', $coupon_ids);
            $update['coupon_fees'] = implode(',', $coupon_fees);
        }
        $updatePayRet = $payService->updatePayInfoById($payId, $update);
        $updateOrderDataRet = false;
        if(BaseService::checkRetIsOk($updatePayRet)) {
            $rest = $payService->alreadyPay($pay_id);
            if(BaseService::checkRetIsOk($rest)) {
                //将订单状态更新成功
                if(!empty($order_ids)) {
                    $orderIdArr = explode(',', $order_ids);
                    $updateOrderWhere['id'] = $orderIdArr;
                    $updateOrderData['status'] = OrderModel::PAY_STATUS;
                    $updateRet = $orderService->updateAllByParams($updateOrderWhere, $updateOrderData);
                    $orderIdArr = explode(',', $order_ids);
                    $updateUserProjectWhere['order_id'] = $orderIdArr;
                    $updateUserProjectData['status'] = UserProjectModel::ONLINE_STATUS;
                    $userProjectService = new UserProjectService();
                    $updateRet = $userProjectService->updateAllByParams($updateUserProjectWhere, $updateUserProjectData);
                    if(BaseService::checkRetIsOk($updateRet)) {
                        $updateOrderDataRet = false;
                    }
                } else{
                    $updateOrderDataRet = false;
                }
            } else{
                $updateOrderDataRet = false;
            }
        }
        //支付成功后未更新成功支付单号的话通过任务处理,将一个或多个值 value 插入到列表 key 的表尾(最右边)
        if(!$updateOrderDataRet) {
            $commonService = new CommonService();
            $key = Yii::$app->params['rediskey']['pay']['notUpdateOrderStatus'];
            $redisLpushRet = $commonService->redisRpush($key, $payId);
            if(!BaseService::checkRetIsOk($redisLpushRet)) {
                DmpLog::debug("redisLpush:notUpdateOrderStatus:" . $payId);
            }
        } else {
            //支付完成的数据写入到支付详情数据里面,将一个或多个值 value 插入到列表 key 的表尾(最右边)
            $commonService = new CommonService();
            $key = Yii::$app->params['rediskey']['pay']['payDetail'];
            $redisLpushRet = $commonService->redisRpush($key, $payId);
            if(!BaseService::checkRetIsOk($redisLpushRet)) {
                DmpLog::debug("redisLpush:payDetail:" . $payId);
            }
        }
        return $data;
    }
}