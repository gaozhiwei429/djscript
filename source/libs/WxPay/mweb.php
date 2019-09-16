<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "./lib/WxPayApi.php";
require_once "./lib/WxPayDataBase.php";
require_once "./lib/WxPayConfig.php";
require_once "NativePay.php";

/**
 * 流程：
 * 1、调用统一下单，取得mweb_url，通过mweb_url调起微信支付中间页
 * 2、用户在微信支付收银台完成支付或取消支付
 * 3、支付完成之后，微信服务器会通知支付成功
 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */
$notify = new NativePay();
$input = new \lib\WxPayDataBase();
$input->SetBody("test");
$input->SetAttach("test");
$input->SetOut_trade_no(\lib\WxPayConfig::MCHID.date("YmdHis"));
$input->SetTotal_fee("100");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
//$input->SetUser_ip($_SERVER["REMOTE_ADDR"]);
$input->SetNotify_url("http://api.wbaole.com/wxPayDemo/notify.php");
$input->SetTrade_type("MWEB");
$input->SetScene_info('{"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}');
//var_dump($input->GetTrade_type());die;
//echo "<pre>";
//var_dump($input);die;
$result = $notify->GetH5PayUrl($input);
//echo "<pre>";
//var_dump($result);die;
$url = $result["mweb_url"];
//echo $url;die;
header("Location:$url");
?>