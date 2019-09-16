<?php

namespace appcomponents\modules\pay;
use appcomponents\modules\order\models\OrderModel;
use appcomponents\modules\order\OrderService;
use appcomponents\modules\pay\models\PayDetailModel;
use appcomponents\modules\pay\models\PayModel;
use source\libs\AliPay\aop\AopClient;
use source\libs\AliPay\aop\request\AlipayTradeWapPayRequest;
use source\libs\Common;
use source\libs\DmpLog;
use source\libs\TwoCode;
use source\libs\DmpRedis;
use source\libs\WxPay\lib\WxPayApi;
use source\libs\WxPay\lib\WxPayConfig;
use source\libs\WxPay\lib\WxPayUnifiedOrder;
use source\libs\WxPay\NativePay;
use source\libs\WxPay\TwoWxPayNativePay;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
/**
 * pay module definition class
 */
class PayService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\pay\controllers';

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }
    /**
     * 根据主支付单编号查询主支付单表详情数据查询
     * @param $order_no
     * @return array
     */
    public function getPayInfoByMyTradeNo($my_trade_no) {
        if(!empty($my_trade_no)) {
            $payModel = new PayModel();
            $payParams[] = ['=', 'my_trade_no', $my_trade_no];
            $payInfo = $payModel->getInfoByParams($payParams);
            if(!empty($payInfo)) {
                return BaseService::returnOkData($payInfo);
            }
        }
        return BaseService::returnErrData([], 54000, "当前支付数据不存在");
    }
    /**
     * 根据主支付单编号查询主支付单表详情数据查询
     * @param $order_no
     * @return array
     */
    public function getPayInfoByByParams($payParams) {
        if(!empty($payParams)) {
            $payModel = new PayModel();
            $payInfo = $payModel->getInfoByParams($payParams);
            if(!empty($payInfo)) {
                return BaseService::returnOkData($payInfo);
            }
        }
        return BaseService::returnErrData([], 55500, "当前支付数据请求参数有误");
    }
    /**
     * 根据子支付单编号查询主支付单表详情数据查询
     * @param $order_no
     * @return array
     */
    public function getPayDetailInfoByParams($payParams) {
        if(!empty($payParams)) {
            $payDetailModel = new PayDetailModel();
            $payDetailInfo = $payDetailModel->getInfoByParams($payParams);
            if(!empty($payDetailInfo)) {
                return BaseService::returnOkData($payDetailInfo);
            }
        }
        return BaseService::returnErrData([], 57000, "当前子支付数据不存在");
    }
    /**
     * 【主支付单号（19位）】 时间（system.currentTimeMillis() 13位）+随机数6位
     * 生成订单号规则
     * @return string
     */
    public function createMyTradeNo($length=19) {
        $commonObj = new Common();
        $randNumberLen = $length-19;
        $randNumberStr = "";
        if($randNumberLen>0) {
            $randNumberStr = $commonObj::getRandChar($randNumberLen, true);
        }
        $my_trade_no = $commonObj->createLongNumberNo($length).$randNumberStr;
        $ret = $this->getPayInfoByMyTradeNo($my_trade_no);
        if(!BaseService::checkRetIsOk($ret)) {
            return $my_trade_no;
        }
        return $this->createMyTradeNo($length);
    }
    /**
     * 添加主支付数据
     * @param $userId
     * @param $orderIdArr
     * @param $totalAmoutArr
     * @param $titleArr
     * @param int $address_id 收货地址
     * @param int $pay_type 支付方式：1支付宝，2微信
     * @param int $pay_from 支付来源
     * @return array|void
     */
    public function addPayData($userId, $orderIdArr, $totalAmoutArr,$titleArr=[], $address_id, $pay_type=2, $pay_from=1) {
        if($pay_type == 2) {
            return $this->addWchatPay($userId, $orderIdArr, $totalAmoutArr, $titleArr, $address_id, $pay_type, $pay_from);
        } else if($pay_type == 1) {
            return $this->addAliPay($userId, $orderIdArr, $totalAmoutArr, $titleArr, $address_id, $pay_type, $pay_from);
        } else{
            return $this->addBackPay($userId, $orderIdArr, $totalAmoutArr, $titleArr, $address_id, $pay_type, $pay_from);
        }
    }
    /**
     * 创建微信主支付记录表数据
     * @param $userId
     * @param $orderIdArr
     * @param $totalAmoutArr
     * @param $titleArr
     * @param int $address_id 收货地址
     * @param int $pay_type
     * @param int $pay_from
     * @return array
     * @throws \yii\db\Exception
     */
    public function addWchatPay($userId, $orderIdArr, $totalAmoutArr,$titleArr=[], $address_id, $pay_type=2, $pay_from=1) {
        if(empty($userId) || (empty($orderIdArr) || !is_array($orderIdArr))
            || (empty($totalAmoutArr) || !is_array($totalAmoutArr))
        ) {
            return BaseService::returnErrData([], 57300, "支付结算请求参数异常");
        }
        $my_trade_no = $this->createMyTradeNo(25);
        $payData['app_id'] = WxPayConfig::APPID;
        $payData['my_trade_no'] = $my_trade_no;
        $payData['user_id'] = $userId;
        $payData['pay_type'] = $pay_type;
        $payData['pay_from'] = $pay_from;
        $payData['address_id'] = $address_id;
        $payData['total_amounts'] = $totalAmoutArr;
        $payData['order_ids'] = $orderIdArr;
        $payData['title'] = $titleArr;
        return $this->addPayDataAllInfo($payData);
    }
    /**
     * 创建支付宝主支付记录表数据
     * @param $userId
     * @param $orderIdArr
     * @param $totalAmoutArr
     * @param $titleArr
     * @param int $address_id 收货地址
     * @param int $pay_type
     * @param int $pay_from
     * @return array
     * @throws \yii\db\Exception
     */
    public function addAliPay($userId, $orderIdArr, $totalAmoutArr,$titleArr=[], $address_id, $pay_type=1, $pay_from=1) {
        $my_trade_no = $this->createMyTradeNo(25);
        $payData['app_id'] = Yii::$app->params['pay']['alipay']['app_id'];
        $payData['my_trade_no'] = $my_trade_no;
        $payData['user_id'] = $userId;
        $payData['pay_type'] = $pay_type;
        $payData['pay_from'] = $pay_from;
        $payData['address_id'] = $address_id;
        $payData['total_amounts'] = $totalAmoutArr;
        $payData['order_ids'] = $orderIdArr;
        $payData['title'] = $titleArr;
        return $this->addPayDataAllInfo($payData);
    }
    /*
     * 银联支付
     */
    public function addBackPay($orderIdArr, $totalAmoutArr, $address_id, $pay_type=3, $pay_from=1) {

    }
    /**
     * 添加批量支付的订单数据处理
     * @param $payData
     * @return array
     * @throws \yii\db\Exception
     */
    public function addPayDataAllInfo($payData) {
        if(!empty($payData)) {
            $payModel = new PayModel();
            $payDetailModel = new PayDetailModel();
            $payDetailInfo = [];
            $payInfo = [];
            if(isset($payData['order_ids']) && is_array($payData['order_ids'])) {
                $payInfo['app_id'] = isset($payData['app_id']) ? $payData['app_id'] : "";
                $payInfo['my_trade_no'] = isset($payData['my_trade_no']) ? $payData['my_trade_no'] : "";
                $payInfo['user_id'] = isset($payData['user_id']) ? $payData['user_id'] : "";
                $payInfo['pay_type'] = isset($payData['pay_type']) ? $payData['pay_type'] : 2;
                $payInfo['pay_from'] = isset($payData['pay_from']) ? $payData['pay_from'] : 1;
                $payInfo['address_id'] = isset($payData['address_id']) ? $payData['address_id'] : 0;
                $payInfo['total_amounts'] = (isset($payData['total_amounts']) && is_array($payData['total_amounts'])) ? implode(',', $payData['total_amounts']) : "";
                $payInfo['order_ids'] = (isset($payData['order_ids']) && is_array($payData['order_ids'])) ? implode(',', $payData['order_ids']) : "";
                $payInfo['title'] = (isset($payData['title']) && is_array($payData['title'])) ? implode(',', $payData['title']) : "";
            }
            if((!empty($payInfo) && is_array($payInfo))) {
                $inserPayId = $payModel->addInfo($payInfo);
                foreach($payData['order_ids'] as $key=>$order_id) {
                    $payDetailInfo[] = [
                        'app_id' => isset($payData['app_id']) ? $payData['app_id'] : "",
                        'my_trade_no' => isset($payData['my_trade_no']) ? $payData['my_trade_no'] : "",
                        'user_id' => isset($payData['user_id']) ? $payData['user_id'] : "",
                        'pay_type' => isset($payData['pay_type']) ? $payData['pay_type'] : 2,
                        'pay_from' => isset($payData['pay_from']) ? $payData['pay_from'] : 1,
                        'address_id' => isset($payData['address_id']) ? $payData['address_id'] : 0,
                        'total_amount' => (isset($payData['total_amounts']) && isset($payData['total_amounts'][$key]) && is_array($payData['total_amounts'][$key]))
                            ? floatval($payData['total_amounts'][$key]) : 0,
                        'order_id' => $order_id,
                        'pay_id' => $inserPayId,
                        'title' => (isset($payData['title']) && isset($payData['title'][$key]) && is_array($payData['title'][$key]))
                            ? trim($payData['title'][$key]) : 0,
                    ];
                }
                $inserPayDetailId = $payDetailModel->addAll($payDetailInfo);
                $tr = Yii::$app->db->beginTransaction();
                if($inserPayId && $inserPayDetailId){
                    $tr->commit();
                    return BaseService::returnOkData($inserPayId);
                }else{
                    $tr->rollBack();
                    return BaseService::returnErrData([], 59200, "系统异常");
                }
            }
        }
        return BaseService::returnErrData([], 519200, "系统异常");
    }
    /**
     * 更新支付主数据
     * @param $id
     * @param $payData
     * @return array
     */
    public function updatePayInfoById($id, $payData) {
        $payParams[] = ['=', 'id', $id];
        $payRet = $this->getPayInfoByByParams($payParams);
        if(BaseService::checkRetIsOk($payRet)) {
            $payModel = new PayModel();
            $updatePayRet = $payModel->updateInfo($id, $payData);
            if($updatePayRet) {
                return BaseService::returnOkData($updatePayRet);
            }
        }
        return BaseService::returnErrData([], 517000, "更新支付数据异常");
    }
    /**
     * 微信H5支付
     */
    public function WxMwebPay($payData) {
        try {
            DmpLog::debug("WxMwebPay". json_encode($payData));
            $notify = new NativePay();//NativePay();
            $input = new WxPayUnifiedOrder();//WxPayUnifiedOrder();
            $body = isset($payData['body']) ? mb_substr($payData['body'],0,40,'utf-8') : '往全保技术服务';
            $input->SetBody($body);
            $input->SetAttach(isset($payData['attach']) ? $payData['attach'] : '');
            $input->SetOut_trade_no(isset($payData['my_trade_no']) ? $payData['my_trade_no'] : WxPayConfig::MCHID.date("YmdHis"));//商户订单号 WxPayConfig::MCHID.date("YmdHis")
            $input->SetTotal_fee(isset($payData['total_amount']) ? $payData['total_amount'] : 0);//单位分
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 60*60*30));
            $input->SetGoods_tag(isset($payData['goods_tag']) ? $payData['goods_tag'] : '');
            $input->SetNotify_url(WxPayConfig::NOTIFY_URL);//"http://api.wbaole.com/pay/pay/wx-pay-notify-call-back"
            $input->SetTrade_type("MWEB");
            $input->SetScene_info('{"h5_info": {"type":"Wap","wap_url": "http://api.wbaole.com","wap_name": "'.$body.'"}}');

            $result = $notify->GetH5PayUrl($input);
            DmpLog::debug("GetH5PayUrl");
            $wxPayApi = new WxPayApi();
            $getsignkey = $wxPayApi->getsignkey($input);
            DmpLog::debug("getsignkey". json_encode($getsignkey));
//        DmpLog::debug($result);
//        DmpLog::debug("====================");
//        DmpLog::debug($input);

            $url = isset($result["mweb_url"]) ? $result["mweb_url"] : '';
//        header("Location:$url");
            return $url;
        } catch (BaseException $e){
            DmpLog::debug("wex-web-pay-exception");
            DmpLog::debug($e);
        }
    }
    /**
     * 阿里H5支付
     */
    public function AliMwebPay($payData) {
        try {
            $config = Yii::$app->params['pay']['alipay'];
            $appid = isset($config['app_id']) ? $config['app_id'] : "";//'2019031463529632';  //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
            $returnUrl = isset($config['return_url']) ? $config['return_url'] : "";//'http://alipay.wbaole.com/alipay/return.php';     //付款成功后的同步回调地址
            $notifyUrl = isset($config['notify_url']) ? $config['notify_url'] : "";//'http://alipay.wbaole.com/alipay/notify.php';     //付款成功后的异步回调地址
            $signType = isset($config['sign_type']) ? trim($config['sign_type']) : "RSA2";//'支付测试';    //订单标题
            $outTradeNo = isset($payData['my_trade_no']) ? trim($payData['my_trade_no']) : uniqid();//uniqid();     //你自己的商品订单号
            $payAmount = isset($payData['total_amount']) ? floatval($payData['total_amount']/100) : 0;// 0.01;          //付款金额，单位:元
            $orderName = isset($payData['body']) ? trim($payData['body']) : "打印机相关的产品购买";//'支付测试';
            $private_key=isset($config['private_key']) ? $config['private_key'] : "";//'MIIEpQIBAAKCAQEApBzKzqxtvKk+cu+C8Nu2MLCuPrqXhyP9e6v5BkzK4KGy6wvfsb8jFZMN0Hu79R56ag8vXtVIN25BXqDVlGHT/GxaNE15hyt/qHItM8O4ZufGUjkoXXjnTdF5kSV+DL6tgWIPnM8QBXsJZBrxJ0btS9HKU7TOByceIgu25/kagO457HbTOmIh0I4d+83xMYths1NLAaKVtBDBOZwtsiiuePO5Nfi9ITbW7zsKtKyniUW9nUMCOKOheNsgsLmIeXkQB+bcKil8pIsRMYHqm03YpLbwPEAK9RdSycaC82QXftfEZv7YXO1qyadzvevmK8VEPN8f1I+zY13LAAoqa/l3EwIDAQABAoIBAQCWX1NtQA1k+uafxdqC+67aumlOTQae61lOQmtxXFfgsAkS2dRkN7DcCdx0lYvJs92S5MsQ5/i94abAIDrTNThaaKXWms6qDi9wlv3YGpQSpnjPylO0Ih4+7ZuoZWQ0JVsx3DpWAly5yNz3/r/Mr26rNFVhkZ/eafdwVq+HqnagfzBDSDmk/Utomzz5joMATa3MFsWIJxlub9qSizGZaEiWxMTMMMGq1b7w5rQoCxQ4iju3o2QOMLPP1bXgnnEYIYrb+9IPZV7FAhkUrnAxQA0X2ViDAZayTzvh/5gfhDOMXgCiBvL78Gu81CSnvpKiE5mcY6MAR/lZqPbe8D+mmSXhAoGBANb1JOox2bDfWNCu4hHpf09Ux2xv5OZe7dl1dgS/Gu/8iQcHMvcq3wyyCDxxjSsl3G8bM4p1VP2vf4DKbHNipRJ67ScmxJCSv/XCOssSp9vDDXVOq3EIYaecPa4x4giqz+zdWtwusHqkjQSZwHatyZNJFKeAmsmO6DKK/ZmBVP5LAoGBAMNyZwr4l+xRMZhG2Cg8pQpWrsky2ZFDNW/iwC1YraUdsXCjMzmfT0gidASwwamCwHgd2NDl3FquLacEghdWcIcbOV9LPRhuCyFXDF+tjIKOXhBs6SYLjJ9hft0HtiyZcubKe/v2s2LEJusyyXNTfviMDjR6AU23W6Xgv2SGgc1ZAoGBAMoesbpAa3gDWujGOC4thxbaGbYdtHblNfKunu0xoKudol7oZwS/3AF8+X+UKfAzZoVWZ20+jE8JkPNR4w3P6HVq/sk8i8GBK1xzaIMAJLPgQSxXb58WCXTn1ZuQrgAGVQJc1Q2KHUkEptB3neA54vtJ0VD6/RCe/jhgNrr7QCbnAoGAb5ntuNgGeAxM03TwjNlELEke+QguL8I+yyqhLcOLM9NmtLib0XVkYf46XUtI6jAdEvmICpCWaLk4nDv4xLa4/ozPD9j4g/CiLmF0UVXZ+9qrX5mw7+Z8X63eMPvsTD862woYDeHqwKTczERtv4qX3/ipS9G22NewX2GcKByeFdECgYEAj2FiC5koQ/UnUU2gVgezC3gsqKx3Oma5jmbm4hraQ4oqia6R7tp/0KbkCfRB5lNrxgQcm34o5Q3Qs/XymGV3/0cFlAv1ODSTKghPkavVHKttSpTMyP0871MaURowYtPQJcxIYWaolF/iodrVWYBQ5yQQG60PRYrQAcZHlZYdnuM=';		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
            $public_key=isset($config['public_key']) ? $config['public_key'] : "";//'MIIEpQIBAAKCAQEApBzKzqxtvKk+cu+C8Nu2MLCuPrqXhyP9e6v5BkzK4KGy6wvfsb8jFZMN0Hu79R56ag8vXtVIN25BXqDVlGHT/GxaNE15hyt/qHItM8O4ZufGUjkoXXjnTdF5kSV+DL6tgWIPnM8QBXsJZBrxJ0btS9HKU7TOByceIgu25/kagO457HbTOmIh0I4d+83xMYths1NLAaKVtBDBOZwtsiiuePO5Nfi9ITbW7zsKtKyniUW9nUMCOKOheNsgsLmIeXkQB+bcKil8pIsRMYHqm03YpLbwPEAK9RdSycaC82QXftfEZv7YXO1qyadzvevmK8VEPN8f1I+zY13LAAoqa/l3EwIDAQABAoIBAQCWX1NtQA1k+uafxdqC+67aumlOTQae61lOQmtxXFfgsAkS2dRkN7DcCdx0lYvJs92S5MsQ5/i94abAIDrTNThaaKXWms6qDi9wlv3YGpQSpnjPylO0Ih4+7ZuoZWQ0JVsx3DpWAly5yNz3/r/Mr26rNFVhkZ/eafdwVq+HqnagfzBDSDmk/Utomzz5joMATa3MFsWIJxlub9qSizGZaEiWxMTMMMGq1b7w5rQoCxQ4iju3o2QOMLPP1bXgnnEYIYrb+9IPZV7FAhkUrnAxQA0X2ViDAZayTzvh/5gfhDOMXgCiBvL78Gu81CSnvpKiE5mcY6MAR/lZqPbe8D+mmSXhAoGBANb1JOox2bDfWNCu4hHpf09Ux2xv5OZe7dl1dgS/Gu/8iQcHMvcq3wyyCDxxjSsl3G8bM4p1VP2vf4DKbHNipRJ67ScmxJCSv/XCOssSp9vDDXVOq3EIYaecPa4x4giqz+zdWtwusHqkjQSZwHatyZNJFKeAmsmO6DKK/ZmBVP5LAoGBAMNyZwr4l+xRMZhG2Cg8pQpWrsky2ZFDNW/iwC1YraUdsXCjMzmfT0gidASwwamCwHgd2NDl3FquLacEghdWcIcbOV9LPRhuCyFXDF+tjIKOXhBs6SYLjJ9hft0HtiyZcubKe/v2s2LEJusyyXNTfviMDjR6AU23W6Xgv2SGgc1ZAoGBAMoesbpAa3gDWujGOC4thxbaGbYdtHblNfKunu0xoKudol7oZwS/3AF8+X+UKfAzZoVWZ20+jE8JkPNR4w3P6HVq/sk8i8GBK1xzaIMAJLPgQSxXb58WCXTn1ZuQrgAGVQJc1Q2KHUkEptB3neA54vtJ0VD6/RCe/jhgNrr7QCbnAoGAb5ntuNgGeAxM03TwjNlELEke+QguL8I+yyqhLcOLM9NmtLib0XVkYf46XUtI6jAdEvmICpCWaLk4nDv4xLa4/ozPD9j4g/CiLmF0UVXZ+9qrX5mw7+Z8X63eMPvsTD862woYDeHqwKTczERtv4qX3/ipS9G22NewX2GcKByeFdECgYEAj2FiC5koQ/UnUU2gVgezC3gsqKx3Oma5jmbm4hraQ4oqia6R7tp/0KbkCfRB5lNrxgQcm34o5Q3Qs/XymGV3/0cFlAv1ODSTKghPkavVHKttSpTMyP0871MaURowYtPQJcxIYWaolF/iodrVWYBQ5yQQG60PRYrQAcZHlZYdnuM=';		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
            $charset=isset($config['charset']) ? $config['charset'] : "UTF-8";
            $format=isset($config['format']) ? $config['format'] : "json";
            $aop = new AopClient();
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = $appid;
            $aop->rsaPrivateKey = $private_key;
            $aop->alipayrsaPublicKey=$public_key;
            $aop->apiVersion = "1.0";
            $aop->signType =$signType;

            $aop->charset= $charset;
            $aop->format= $format;
            $request = new AlipayTradeWapPayRequest();
            $request->setNotifyUrl($notifyUrl);
            $request->setReturnUrl($returnUrl);

            $request->setBizContent("{" ."\"out_trade_no\":\"$outTradeNo\","."\"product_code\":\"QUICK_WAP_WAY\","."\"total_amount\":\"$payAmount\"," . "\"subject\":\"$orderName\"," . "\"body\":\"$orderName\"" ."," . "\"timeout_express\":\"30m\"" ."}");//."\"quit_url\":\"https://www.baidu.com/\""
            $result = $aop->pageExecute($request,"GET");
            return $result;
//            $url = htmlspecialchars($result);
//            DmpLog::debug($url);
//            return $url;
        } catch (BaseException $e){
            DmpLog::debug("wex-web-pay-exception");
            DmpLog::debug($e);
        }
    }
    /**
     * 微信支付回调
     * @return array
     * @throws \source\libs\WxPay\lib\WxPayException
     */
    public function WxPayNotifyCallBack(){
        $notify = new PayNotifyCallBackService();
        $payNotifyCallBackRet = $notify->Handle(false);
        $ret = $notify->GetReturn_code();
        if($ret == 'SUCCESS') {
            return true;
        }else {
            return false;
        }
//        return $payNotifyCallBackRet;
    }
    /**
     * 支付流水表数据添加
     * @param $data
     * @return array
     */
    public function createPayData($data) {
        $payModel = new PayModel();
        $addPayRet = $payModel->addInfo($data);
        if($addPayRet) {
            return BaseService::returnOkData($addPayRet);
        }
        return BaseService::returnErrData($addPayRet);
    }
    /**
     * 批量添加支付详情数据
     * @param $payDetailArrs
     * @return array
     */
    public function addPayDetailList($payDetailArrs) {
        if(!empty($payDetailArrs) && is_array($payDetailArrs)) {
            $payDetailModel = new PayDetailModel();
            $payAddAll = $payDetailModel->addAll($payDetailArrs);
            if($payAddAll) {
                return BaseService::returnOkData($payAddAll);
            }
        }
        return BaseService::returnErrData([], 532900, "批量创建支付详情数据异常");
    }
    /**
     * 已支付的主订单需要将对应的支付详情记录和关联的订单数据更新为已支付
     * @param $pay_id
     * @return array
     * @throws \yii\db\Exception
     */
    public function alreadyPay($pay_id) {
        $tr = Yii::$app->db->beginTransaction();
        //添加两条支付详情记录数据
        $payParams[] = ['=', 'id', $pay_id];
        $payInfoRet = $this->getPayInfoByByParams($payParams);
        $updateOrder = true;
        $addPayDetail = true;
        $payDetailArr = [];
        if(BaseService::checkRetIsOk($payInfoRet)) {
            $payInfo = BaseService::getRetData($payInfoRet);
            $orderIds = (isset($payInfo['order_ids']) && !empty($payInfo['order_ids'])) ? explode(',', $payInfo['order_ids']) : [];
            $totalAmounts = (isset($payInfo['total_amounts']) && !empty($payInfo['total_amounts'])) ? explode(',', $payInfo['total_amounts']) : [];
            $user_id = (isset($payInfo['user_id']) && !empty($payInfo['user_id'])) ? $payInfo['user_id'] : 0;
            $my_trade_no = (isset($payInfo['my_trade_no']) && !empty($payInfo['my_trade_no'])) ? $payInfo['my_trade_no'] : "";
            $receipt_amount = (isset($payInfo['receipt_amount']) && !empty($payInfo['receipt_amount'])) ? $payInfo['receipt_amount'] : 0;
            $buyer_id = (isset($payInfo['buyer_id']) && !empty($payInfo['buyer_id'])) ? $payInfo['buyer_id'] : "";
            $trade_no = (isset($payInfo['trade_no']) && !empty($payInfo['trade_no'])) ? $payInfo['trade_no'] : "";
            $status = (isset($payInfo['status']) && !empty($payInfo['status'])) ? $payInfo['status'] : PayDetailModel::PAY_STATUS;
            $callbak_time = (isset($payInfo['callbak_time']) && !empty($payInfo['callbak_time'])) ? $payInfo['callbak_time'] : "";
            $app_id = (isset($payInfo['app_id']) && !empty($payInfo['app_id'])) ? $payInfo['app_id'] : "";
            $callbak_result = (isset($payInfo['callbak_result']) && !empty($payInfo['callbak_result'])) ? $payInfo['callbak_result'] : "";
            $titleArr = (isset($payInfo['title']) && !empty($payInfo['title'])) ? explode(',', $payInfo['title']) : [];
            $pay_type = (isset($payInfo['pay_type']) && !empty($payInfo['pay_type'])) ? $payInfo['pay_type'] : 0;
            $pay_from = (isset($payInfo['pay_from']) && !empty($payInfo['pay_from'])) ? $payInfo['pay_from'] : 1;
            $coupon_amount = (isset($payInfo['coupon_amount']) && !empty($payInfo['coupon_amount'])) ? $payInfo['coupon_amount'] : 0;
            $coupon_count = (isset($payInfo['coupon_count']) && !empty($payInfo['coupon_count'])) ? $payInfo['coupon_count'] : 0;
            $coupon_ids = (isset($payInfo['coupon_ids']) && !empty($payInfo['coupon_ids'])) ? $payInfo['coupon_ids'] : "";
            $coupon_fees = (isset($payInfo['coupon_fees']) && !empty($payInfo['coupon_fees'])) ? $payInfo['coupon_fees'] : "";
            $my_coupon_ids = (isset($payInfo['my_coupon_ids']) && !empty($payInfo['my_coupon_ids'])) ? $payInfo['my_coupon_ids'] : "";
            $my_coupon_fees = (isset($payInfo['my_coupon_fees']) && !empty($payInfo['my_coupon_fees'])) ? $payInfo['my_coupon_fees'] : "";
            $address_id = (isset($payInfo['address_id']) && !empty($payInfo['address_id'])) ? $payInfo['address_id'] : 0;

            //添加支付记录
            if(!empty($totalAmounts)) {
                foreach($totalAmounts as $k=>$total_amount) {
                    $orderId = isset($orderIds[$k]) ? $orderIds[$k] : 0;
                    $payDetailArr[$orderId] = [
                        'user_id' => $user_id,
                        'my_trade_no' => $my_trade_no,
                        'order_id' => $orderId,//isset($orderIds[$k]) ? $orderIds[$k] : 0,
                        'total_amount' => floatval($total_amount),
                        'receipt_amount' => floatval($receipt_amount),
                        'buyer_id' => $buyer_id,
                        'trade_no' => $trade_no,
                        'status' => $status,
                        'callbak_time' => $callbak_time,
                        'pay_id' => $pay_id,
                        'pay_type' => $pay_type,
                        'pay_from' => $pay_from,
                        'app_id' => $app_id,
                        'title' => isset($titleArr[$k]) ? $titleArr[$k] : "",
                        'callbak_result' => $callbak_result,
                        'coupon_amount' => floatval($coupon_amount),
                        'coupon_count' => $coupon_count,
                        'coupon_ids' => $coupon_ids,
                        'coupon_fees' => $coupon_fees,
                        'my_coupon_ids' => $my_coupon_ids,
                        'my_coupon_fees' => $my_coupon_fees,
                        'address_id' => $address_id,
                    ];
                }
            }
            $payDetailParamsWhere[] = ['=', 'pay_id', $pay_id];
            $payDetailInfoRet = $this->getPayDetailInfoByParams($payDetailParamsWhere);
            if(!empty($payDetailArr)) {
                if(!BaseService::checkRetIsOk($payDetailInfoRet)) {
                    $addPayDetailRet = $this->addPayDetailList($payDetailArr);
                } else {
                    if(!empty($orderIds)) {
                        foreach($orderIds as $orderId) {
                            if($orderId && isset($payDetailArr[$orderId])) {
                                $payDetailParams['order_id'] =  $orderId;
                                $addPayDetailRet = $this->updateAllDataListByParams($payDetailParams, $payDetailArr[$orderId]);
                                if(!BaseService::checkRetIsOk($addPayDetailRet)) {
                                    $addPayDetail = false;
                                }
                            }
                        }
                    } else {
                        $addPayDetailRet = BaseService::returnErrData([], 546200, "数据更新异常");
                    }
                }
                if(!BaseService::checkRetIsOk($addPayDetailRet)) {
                    $addPayDetail = false;
                }
            }

            //更新订单详情数据为已支付
            if(!empty($orderIds) && $status==PayModel::PAY_STATUS) {
                $orderService = new OrderService();
                $orderParams['id'] = $orderIds;
                $updateData = [
                    'status'=> OrderModel::PAY_STATUS,
                    'pay_time'=>date('Y-m-d H:i:s'),
                ];
                $ret = $orderService->updateAllByParams($orderParams, $updateData);
                if(BaseService::checkRetIsOk($ret)) {
                    $updateOrder = true;
                }
            }
        }
        if($updateOrder && $addPayDetail){
            $tr->commit();
            return BaseService::returnOkData($addPayDetail);
        }else{
            $tr->rollBack();
            return BaseService::returnErrData([], 549100, "更新失败");
        }
    }
    public function updateAlreadyPayData($pay_id) {

    }
    /**
     * C端主订单记录数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $payModel = new PayModel();
        $payList = $payModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($payList)) {
            return BaseService::returnOkData($payList);
        }
        return BaseService::returnErrData([], 551000, "暂无数据");
    }
    /**
     * 更新支付数据
     * @param int $start
     * @param int $end
     */
    public function AlreadyPayNotUpdateOrderStatus($start=1, $end=10){
        $key = Yii::$app->params['rediskey']['pay']['notUpdateOrderStatus'];
        //获取支付成功之后的订单
        $dmpRedis = new DmpRedis();
        $payIds = $dmpRedis->lrange($key, $start, $end);
        $orderIds = [];
        //获取支付表的支付结果数据
        if(!empty($payIds)) {
            $params[] = ['in', 'id', $payIds];
            $ret = $this->getList($params, [], 1, -1);
            if(BaseService::checkRetIsOk($ret)) {
                $alreadyPayData = BaseService::getRetData($ret);
                if(isset($alreadyPayData['dataList']) && !empty($alreadyPayData['dataList'])) {
                    foreach($alreadyPayData['dataList'] as $payInfo) {
                        //如果是已支付的话->更新支付记录表然后->更新对应的订单记录表数据->创建支付详情数据结果
                        if(isset($payInfo['status']) && $payInfo['status']==PayModel::PAY_STATUS && isset($payInfo['order_ids'])) {
                            $orderIdArr = explode(',', $payInfo['order_ids']);
                            foreach($orderIdArr as $orderId) {
                                $orderIds[] = $orderId;
                            }
                        }
                    }
                }
            } else {
                //没有支付数据的话就将payid从队列的左边出队一个元素
                $dmpRedis = new DmpRedis();
                for($i=1; $i++; $i<=count($payIds)) {
                    $dmpRedis->LpopRedis($key);
                }
            }
        }
    }
    /**
     * 获取支付数据详情
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        if(!empty($params) && is_array($params)) {
            $payModel = new PayModel();
            $payInfo = $payModel->getInfoByParams($params);
            if(!empty($payInfo)) {
                return BaseService::returnOkData($payInfo);
            }
        }
        return BaseService::returnErrData([], 556200, "当前支付数据不存在");
    }
    /**
     * 获取支付数据详情
     * @param $params
     * @return array
     */
    public function getDetailInfo($params) {
        if(!empty($params) && is_array($params)) {
            $payModel = new PayDetailModel();
            $payInfo = $payModel->getInfoByParams($params);
            if(!empty($payInfo)) {
                return BaseService::returnOkData($payInfo);
            }
        }
        return BaseService::returnErrData([], 557700, "当前支付详情数据不存在");
    }
    /**
     * 获取支付数据详情
     * @param $params
     * @return array
     */
    public function updateDetailInfoByParams($params, $updateData) {
        if(!empty($params) && is_array($params) && !empty($updateData) && is_array($updateData)) {
            $payModel = new PayDetailModel();
            $updateDetailInfo = $payModel->updateInfoByParams($params, $updateData);
            if(!empty($updateDetailInfo)) {
                return BaseService::returnOkData($updateDetailInfo);
            }
        }
        return BaseService::returnErrData([], 559200, "更新支付数据详情失败");
    }
    /**
     * 批量更新支付详情数据
     * @param $params
     * @return array
     */
    public function updateAllDataListByParams($params, $updateData) {
        if(!empty($params) && is_array($params) && !empty($updateData) && is_array($updateData)) {
            $payModel = new PayDetailModel();
            $updateDetailInfo = $payModel->updateAllDataListByParams($params, $updateData);
            if($updateDetailInfo) {
                return BaseService::returnOkData($updateDetailInfo);
            }
        }
        return BaseService::returnErrData([], 560700, "更新支付数据详情失败");
    }

    /**
     * 微信扫码支付
     * 流程：
     * 1、调用统一下单，取得code_url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、支付完成之后，微信服务器会通知支付成功
     * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     */
    public function WxQrcodePay($payData) {
        $payNum = (isset($payData['order_ids']) && $payData['order_ids']) ? $payData['order_ids'] :
            ((isset($payData['my_trade_no']) && $payData['my_trade_no']) ? $payData['my_trade_no'] : $payData['my_trade_no']);

        $payData['app_id'] = \source\libs\WxPay\twolib\WxPayConfig::APPID;//支平台的商户号
        $notify = new TwoWxPayNativePay();
        $url2 = $notify->GetPrePayUrl($payNum);
        $input = new \source\libs\WxPay\twolib\WxPayUnifiedOrder();
        $body = isset($payData['body']) ? $payData['body'] : '';
        $input->SetBody($body);
        if(isset($payData['attach']) && !empty($payData['attach'])) {
            $input->SetAttach($payData['attach']);
        } else {
            $input->SetAttach($body);
        }

        $input->SetOut_trade_no(isset($payData['my_trade_no']) ? $payData['my_trade_no'] : \source\libs\WxPay\twolib\WxPayConfig::MCHID.date("YmdHis"));
        $input->SetTotal_fee(isset($payData['total_amount']) ? $payData['total_amount']*100 : 0);//单位分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60*60*30));
        $input->SetGoods_tag(isset($payData['goods_tag']) ? $payData['goods_tag'] : '');
        $input->SetNotify_url(\source\libs\WxPay\twolib\WxPayConfig::NOTIFY_URL);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($payNum);
        $result = $notify->GetPayUrl($input);
        DmpLog::debug($result);
        $url1 = isset($result["code_url"]) ? $result["code_url"] : "";
        $url2 = "";
        $data = [];
        $twoCode = new TwoCode();
        if($url2) {
            $url2 = urlencode($url2);
//            $url2 = "http://paysdk.weixin.qq.com/example/qrcode.php?data=$url2";
            $rest = $twoCode->create($url2);
            $data['TwoUrl'] = "";
            if(BaseService::checkRetIsOk($rest)) {
                $data['TwoUrl'] = BaseService::getRetData($rest);
            }
        }
        if($url1) {
            $rest = $twoCode->createLogonQrcode($url1);
            $data['firstUrl'] = "";
            if(BaseService::checkRetIsOk($rest)) {
                $data['firstUrl'] = BaseService::getRetData($rest);
            }
        }
        if(!empty($data)) {
            return BaseService::returnOkData($data);
        } else{
            return BaseService::returnErrData($data, 567000, "微信扫码支付异常，请更换请求参数trade_no再试");
        }
    }
    /**
     * 阿里扫码支付
     * @param $payData
     * @return array
     */
    public function AliQrcodePay($payData) {
        if(!empty($payData)) {
            return BaseService::returnOkData($payData);
        } else{
            return BaseService::returnErrData($payData, 568200, "阿里扫码异常，请更换请求参数trade_no再试");
        }
    }
    /**
     * //如果是已支付的话->更新支付记录表然后->更新对应的订单记录表数据->创建支付详情数据结果
     * 批量支付id同步支付结果数据
     */
    public function synPayOrder() {

    }

    public function qrcodePay($pay_type, $pay_from, $payData) {

    }
}
