<?php
/*13:44 2019-1-16
 * @author 煜雨
 * alipay.trade.precreate(统一收单线下交易预创建)
 https://docs.open.alipay.com/api_1/alipay.trade.precreate
  
 */

require_once '../AopSdk.php';

$aop = new AopClient ();
$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
$aop->appId = '2019031463539623';
$aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEA618KQQTKwCqC+hQDGwTsp6Z7ARK95jHuDs/yC5VaHdhn8xYiuIithI75kS5lBcHt/3Gmv6bzQ/GWfZSxc64UKBV4DE3jp018pvmDD0fr/OVArq+fMiYaxu7liL8vJPFaq5jDosM8wjFJVIxtP3YfDIxrnCfKiIR5S9E81kL1Tfsp11pO1G5CQC1LdrPdQTpHXb2RZcJA/2i0bsDofPNhfMP+pW4mFI7x2ZlHjIt5Q8lvlj95NPfl0+9gKnuRmbaWuXF7bzB9V7mYPnl9ci02neBbbiO3zm3AUHAO2ocxOYRuAUrCIgAZ3/1ymZI242cQqjvdumdsEL+kVqBU9hRjawIDAQABAoIBAAIhNQWLlo4QlGjQ34pSDBYkQn6q/b7kesP1i7ohGtSkTaR8ccn7qp5FcOqoYc+ghpfGHn0jdJYLzJHMU6K5U4NdVl2+Iq4z3EnifhYYT4FVTbWG70jT4XAlXzxShomuCWT+F98UJyuWqJQ0RRTK69MAPkUGcyWzFSnjdcp2hqOy+qe5dZ6LH8iQr1X6IN9Oh0GTd8AF5tBiwCNOSG7fJE3JzwpGh9lM5WkYIuX8QVP/U6vvrpRg0q9sR8p9OyzvE7jZ2MVT8i2lwA4wuo9qtsGTRYR5qBeYmPPb2NUfoo4yCU6Qie3NJw7lERKn9OCF+JuXDuG7yUkhRJCaBvo/YiECgYEA9kAvGuKJx8HhOV0/KQwZd5dvjxEWzQXPJ8PpNj3Yir7zLXHOdM0VXmv8NTji+0UsqVF7/1OEiNet00nMblzsSIUJ+FukwNAs9OiriA8VGNOiTIt0uFCbQHbjgsjwqdnZ3g9/X1WRe0xQlekZEI8LLIqJ6Cl4okhqbjK8tvw0wVECgYEA9LCW+vV09TsuU267kXvBKYYZodlaXPh+IEkYkIHFSSxIeRkLIgbUW74bIdDJKpEb1o9Lq+6o3fVdFkI9UBA3PkNEtRQZjFqu3u+Vg1rGBKBa8TUQUWfwe1X3T6SY0205q77rjPVoot+Z4Y7JpegRJl0kKCpUqXDjzifzVw7fCfsCgYEAo5dMM94fjjc8+rD5zrkdChfuourFbPrY/h/mhIRdoP7t6ljawTmlYo72hB0AndD8tJdPevXu7EHsVpuGViMhTaQkVXv8XaNu2lzf54mtiErAXX2f0vqpyQ+yYSZAy3XQiPdetOTRu0mdl5m9bwS4daSrrTIv1A+rtP275Qxzt6ECgYAG7flYohrt1v843anyNM8DngibzMwCVgc8YtWPQh2UNOzYgR0PmaHp9zhNfNw1mGZxbR13gQrHPmukdbvGyK7H1J/dgPQ4RVkudxqE3c091Ey/CAGuTINC+uxGFSM+2ZkIhB+nnkuYUTzKZoDPcgMKUxjzCj/bjJbm/7qkfIdUgQKBgQCW8sOyjwW2ZStEplj4p15tE+78JwVBUCVUHRIfADtPAKsgHrE7DCfPK09Q+yLt/MHExKpZk+VzpNJrNoYJuFC+VXGqxMlQonSyiQgod+xK/XXToLx/rkU+X4cBmcs380CPa175yvg+fs3WCeEWCmB+KOoJNWEdrkT5FDQlDoXvtg==';
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA618KQQTKwCqC+hQDGwTsp6Z7ARK95jHuDs/yC5VaHdhn8xYiuIithI75kS5lBcHt/3Gmv6bzQ/GWfZSxc64UKBV4DE3jp018pvmDD0fr/OVArq+fMiYaxu7liL8vJPFaq5jDosM8wjFJVIxtP3YfDIxrnCfKiIR5S9E81kL1Tfsp11pO1G5CQC1LdrPdQTpHXb2RZcJA/2i0bsDofPNhfMP+pW4mFI7x2ZlHjIt5Q8lvlj95NPfl0+9gKnuRmbaWuXF7bzB9V7mYPnl9ci02neBbbiO3zm3AUHAO2ocxOYRuAUrCIgAZ3/1ymZI242cQqjvdumdsEL+kVqBU9hRjawIDAQAB';
$aop->apiVersion = "1.0";
$aop->signType ="RSA2";

$aop->charset= "UTF-8";
$aop->format="json";
$request = new AlipayTradeWapPayRequest ();
$time = time();
$request->setNotifyUrl("http://api.wbaole.com/pay/pay/ali-pay-notify-call-back");
$request->setReturnUrl("http://api.wbaole.com/pay/pay/ali-pay-notify-call-back");

$request->setBizContent("{" .
			  "\"out_trade_no\":\"$time\",".
		        "\"product_code\":\"QUICK_WAP_WAY\",".
		        "\"total_amount\":0.01," .
      			"\"subject\":\"煜雨测试手机网站支付\"," .
		        "\"body\":\"煜雨手机网站\"," .
		        "\"quit_url\":\"https://www.baidu.com/\"" .
				"}");
$result = $aop->pageExecute($request,"GET");
//echo json_encode($result);
//var_dump($result);die;
//header("Location: http://www.baidu.com");
//print_r($result);die;
$url = htmlspecialchars($result);
//echo "<pre>";
print_r($url);die;
//header("Location: $result");
//var_dump($result);die;
header("Location: $url");
print_r(htmlspecialchars($result));
?>