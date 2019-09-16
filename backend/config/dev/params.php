<?php

/**
 * 应用配置
 * @文件名称: params.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
$params = [
    'app_secret' => 'AE3423EFD45EAC4DA63C2A',
    'enable_sign'=>false,
    'enable_token' => true,
    'order' => [
        'overdue_time' => 30,//单位分钟
    ],
    'casccloud' => [
        'type'=>[1,2,3,4],// 1 型号 2 配件名称 3 耗材名称 4 耗材颜色
    ],
    //打印机状态的获取接口
    'printer' => [
        'casccloudHost' => 'http://cert.casccloud.com:10251',
        'host' => 'http://119.57.117.241:10244/elastic/esQuery',
        'downloadHost' => 'http://119.57.117.241:10243/sendModuInfo',//向SPS发送模型文件
        'printTypeInfoHost' => "http://cert.casccloud.com:10251/GetPrintTypeInfo.aspx",//佟礼的打印机动态数据接口
        'stockHost' => "http://cert.casccloud.com:10251/GetStock.aspx",//获取库存信息
        'dicHost' => "http://cert.casccloud.com:10251/GetDic.aspx",//获取参数字典
        'appOutApplyOperHost' => "http://cert.casccloud.com:10251/AppOutApplyOper.aspx",//获取产品信息出入库的
    ],
    'pay' => [
        'refer' => "http://api.wbaole.com/pay.html",
        'wx' => [
            'refer' => "http://api.wbaole.com/pay.html",
        ],
        'alipay'=>[
//            $appid = '2019031463529632';  //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
//$returnUrl = 'http://alipay.wbaole.com/alipay/return.php';     //付款成功后的同步回调地址
//$notifyUrl = 'http://alipay.wbaole.com/alipay/notify.php';     //付款成功后的异步回调地址
//$outTradeNo = uniqid();     //你自己的商品订单号
//$payAmount = 0.01;          //付款金额，单位:元
//$orderName = '支付测试';    //订单标题
//$signType = 'RSA2';			//签名算法类型，支持RSA2和RSA，推荐使用RSA2
//$rsaPrivateKey='MIIEpQIBAAKCAQEApBzKzqxtvKk+cu+C8Nu2MLCuPrqXhyP9e6v5BkzK4KGy6wvfsb8jFZMN0Hu79R56ag8vXtVIN25BXqDVlGHT/GxaNE15hyt/qHItM8O4ZufGUjkoXXjnTdF5kSV+DL6tgWIPnM8QBXsJZBrxJ0btS9HKU7TOByceIgu25/kagO457HbTOmIh0I4d+83xMYths1NLAaKVtBDBOZwtsiiuePO5Nfi9ITbW7zsKtKyniUW9nUMCOKOheNsgsLmIeXkQB+bcKil8pIsRMYHqm03YpLbwPEAK9RdSycaC82QXftfEZv7YXO1qyadzvevmK8VEPN8f1I+zY13LAAoqa/l3EwIDAQABAoIBAQCWX1NtQA1k+uafxdqC+67aumlOTQae61lOQmtxXFfgsAkS2dRkN7DcCdx0lYvJs92S5MsQ5/i94abAIDrTNThaaKXWms6qDi9wlv3YGpQSpnjPylO0Ih4+7ZuoZWQ0JVsx3DpWAly5yNz3/r/Mr26rNFVhkZ/eafdwVq+HqnagfzBDSDmk/Utomzz5joMATa3MFsWIJxlub9qSizGZaEiWxMTMMMGq1b7w5rQoCxQ4iju3o2QOMLPP1bXgnnEYIYrb+9IPZV7FAhkUrnAxQA0X2ViDAZayTzvh/5gfhDOMXgCiBvL78Gu81CSnvpKiE5mcY6MAR/lZqPbe8D+mmSXhAoGBANb1JOox2bDfWNCu4hHpf09Ux2xv5OZe7dl1dgS/Gu/8iQcHMvcq3wyyCDxxjSsl3G8bM4p1VP2vf4DKbHNipRJ67ScmxJCSv/XCOssSp9vDDXVOq3EIYaecPa4x4giqz+zdWtwusHqkjQSZwHatyZNJFKeAmsmO6DKK/ZmBVP5LAoGBAMNyZwr4l+xRMZhG2Cg8pQpWrsky2ZFDNW/iwC1YraUdsXCjMzmfT0gidASwwamCwHgd2NDl3FquLacEghdWcIcbOV9LPRhuCyFXDF+tjIKOXhBs6SYLjJ9hft0HtiyZcubKe/v2s2LEJusyyXNTfviMDjR6AU23W6Xgv2SGgc1ZAoGBAMoesbpAa3gDWujGOC4thxbaGbYdtHblNfKunu0xoKudol7oZwS/3AF8+X+UKfAzZoVWZ20+jE8JkPNR4w3P6HVq/sk8i8GBK1xzaIMAJLPgQSxXb58WCXTn1ZuQrgAGVQJc1Q2KHUkEptB3neA54vtJ0VD6/RCe/jhgNrr7QCbnAoGAb5ntuNgGeAxM03TwjNlELEke+QguL8I+yyqhLcOLM9NmtLib0XVkYf46XUtI6jAdEvmICpCWaLk4nDv4xLa4/ozPD9j4g/CiLmF0UVXZ+9qrX5mw7+Z8X63eMPvsTD862woYDeHqwKTczERtv4qX3/ipS9G22NewX2GcKByeFdECgYEAj2FiC5koQ/UnUU2gVgezC3gsqKx3Oma5jmbm4hraQ4oqia6R7tp/0KbkCfRB5lNrxgQcm34o5Q3Qs/XymGV3/0cFlAv1ODSTKghPkavVHKttSpTMyP0871MaURowYtPQJcxIYWaolF/iodrVWYBQ5yQQG60PRYrQAcZHlZYdnuM=';		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310

            'debug'       => false, // 沙箱模式
            //应用ID,您的APPID。
            'app_id' => "2019031463539623",
            //商户私钥，您的原始格式RSA私钥
            'private_key' => "MIIEpAIBAAKCAQEA618KQQTKwCqC+hQDGwTsp6Z7ARK95jHuDs/yC5VaHdhn8xYiuIithI75kS5lBcHt/3Gmv6bzQ/GWfZSxc64UKBV4DE3jp018pvmDD0fr/OVArq+fMiYaxu7liL8vJPFaq5jDosM8wjFJVIxtP3YfDIxrnCfKiIR5S9E81kL1Tfsp11pO1G5CQC1LdrPdQTpHXb2RZcJA/2i0bsDofPNhfMP+pW4mFI7x2ZlHjIt5Q8lvlj95NPfl0+9gKnuRmbaWuXF7bzB9V7mYPnl9ci02neBbbiO3zm3AUHAO2ocxOYRuAUrCIgAZ3/1ymZI242cQqjvdumdsEL+kVqBU9hRjawIDAQABAoIBAAIhNQWLlo4QlGjQ34pSDBYkQn6q/b7kesP1i7ohGtSkTaR8ccn7qp5FcOqoYc+ghpfGHn0jdJYLzJHMU6K5U4NdVl2+Iq4z3EnifhYYT4FVTbWG70jT4XAlXzxShomuCWT+F98UJyuWqJQ0RRTK69MAPkUGcyWzFSnjdcp2hqOy+qe5dZ6LH8iQr1X6IN9Oh0GTd8AF5tBiwCNOSG7fJE3JzwpGh9lM5WkYIuX8QVP/U6vvrpRg0q9sR8p9OyzvE7jZ2MVT8i2lwA4wuo9qtsGTRYR5qBeYmPPb2NUfoo4yCU6Qie3NJw7lERKn9OCF+JuXDuG7yUkhRJCaBvo/YiECgYEA9kAvGuKJx8HhOV0/KQwZd5dvjxEWzQXPJ8PpNj3Yir7zLXHOdM0VXmv8NTji+0UsqVF7/1OEiNet00nMblzsSIUJ+FukwNAs9OiriA8VGNOiTIt0uFCbQHbjgsjwqdnZ3g9/X1WRe0xQlekZEI8LLIqJ6Cl4okhqbjK8tvw0wVECgYEA9LCW+vV09TsuU267kXvBKYYZodlaXPh+IEkYkIHFSSxIeRkLIgbUW74bIdDJKpEb1o9Lq+6o3fVdFkI9UBA3PkNEtRQZjFqu3u+Vg1rGBKBa8TUQUWfwe1X3T6SY0205q77rjPVoot+Z4Y7JpegRJl0kKCpUqXDjzifzVw7fCfsCgYEAo5dMM94fjjc8+rD5zrkdChfuourFbPrY/h/mhIRdoP7t6ljawTmlYo72hB0AndD8tJdPevXu7EHsVpuGViMhTaQkVXv8XaNu2lzf54mtiErAXX2f0vqpyQ+yYSZAy3XQiPdetOTRu0mdl5m9bwS4daSrrTIv1A+rtP275Qxzt6ECgYAG7flYohrt1v843anyNM8DngibzMwCVgc8YtWPQh2UNOzYgR0PmaHp9zhNfNw1mGZxbR13gQrHPmukdbvGyK7H1J/dgPQ4RVkudxqE3c091Ey/CAGuTINC+uxGFSM+2ZkIhB+nnkuYUTzKZoDPcgMKUxjzCj/bjJbm/7qkfIdUgQKBgQCW8sOyjwW2ZStEplj4p15tE+78JwVBUCVUHRIfADtPAKsgHrE7DCfPK09Q+yLt/MHExKpZk+VzpNJrNoYJuFC+VXGqxMlQonSyiQgod+xK/XXToLx/rkU+X4cBmcs380CPa175yvg+fs3WCeEWCmB+KOoJNWEdrkT5FDQlDoXvtg==",
            //异步通知地址
            'notify_url' => "http://api.wbaole.com/pay/pay/ali-pay-notify-call-back",
            //同步跳转
            'return_url' => "http://api.wbaole.com/pay/pay/ali-pay-notify-call-back",
            //编码格式
            'charset' => "UTF-8",
            'format' => "json",
            //签名方式
            'sign_type'=>"RSA2",
            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA618KQQTKwCqC+hQDGwTsp6Z7ARK95jHuDs/yC5VaHdhn8xYiuIithI75kS5lBcHt/3Gmv6bzQ/GWfZSxc64UKBV4DE3jp018pvmDD0fr/OVArq+fMiYaxu7liL8vJPFaq5jDosM8wjFJVIxtP3YfDIxrnCfKiIR5S9E81kL1Tfsp11pO1G5CQC1LdrPdQTpHXb2RZcJA/2i0bsDofPNhfMP+pW4mFI7x2ZlHjIt5Q8lvlj95NPfl0+9gKnuRmbaWuXF7bzB9V7mYPnl9ci02neBbbiO3zm3AUHAO2ocxOYRuAUrCIgAZ3/1ymZI242cQqjvdumdsEL+kVqBU9hRjawIDAQAB",
        ],
        'ccb'=>[
            //商户私钥
            'private_key' => "30819d300d06092a864886f70d010101050003818b0030818702818100ed69fa50b7a04a47f6fb31c87f162dc810d0893ff36085d8cd433d822e8513e31f5c686bd80910df28ccb72f0494b02414dfc6c1578fe077837aad4024fbe532d946c1126c92caa3504b1a45693b4f25677dcad46c13260f3b3928de9a812df4404247a3648ea3d3159fbd8d6c1a3986b8717338af97d5e03b0bf4a1a23adcb9020111",

        ]
    ],
    //非模型的分类id,我的模型库列表搜索的时候用到
    'noModelTypes' =>[
        0,10,20,40,50,60,70,80
    ],
    'gps'=>[
        'baidu'=>[
            'host'=>"http://api.map.baidu.com/geocoder",
            'ak'=>"C765da16031004021f009cf340b21e27",
        ]
    ],
    'project' => [
        'host' => 'http://api.wbaole.com',
    ],
];

$common = require(dirname(__DIR__) . '/common/params.php');

return \yii\helpers\ArrayHelper::merge($common, $params);