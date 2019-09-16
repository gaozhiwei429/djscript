<?php
namespace source\libs\WxPay\lib;
/**
 * 提交JSAPI输入对象
 * 微信支付样例-支付
 * 流程：
 * 1、获取用户openid
 * 2、统一下单
 * 3、在支持成功回调通知中处理成功之后的事宜，见 notify.php
 * 4、注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
use source\libs\DmpLog;

/**
 *
 * JSAPI支付实现类
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 *
 * @author widy
 *
 */
class WxPayJsApiPay extends WxPayDataBase
{
    /**
     *
     * 网页授权接口微信服务器返回的数据，返回样例如下
     * {
     *  "access_token":"ACCESS_TOKEN",
     *  "expires_in":7200,
     *  "refresh_token":"REFRESH_TOKEN",
     *  "openid":"OPENID",
     *  "scope":"SCOPE",
     *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     * 其中access_token可用于获取共享收货地址
     * openid是微信支付jsapi支付接口必须的参数
     * @var array
     */
    public $data = null;
    public $curl_timeout = 60;
    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);//.$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }
    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public function GetJsApiParameters($UnifiedOrderResult)
    {
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            DmpLog::debug("UnifiedOrderResult".json_encode($UnifiedOrderResult));
            throw new WxPayException("参数错误");
        }
        $jsapi = new WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign());
        $parameters = json_encode($jsapi->GetValues());
        return $parameters;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
            && WxPayConfig::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        DmpLog::debug('GetOpenidFromMp'. $res);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    public function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     *
     * 获取地址js参数
     *
     * @return 获取共享收货地址js函数需要的参数，json格式可以直接做参数使用
     */
    public function GetEditAddressParameters()
    {
        $getData = $this->data;
        $data = array();
        $data["appid"] = WxPayConfig::APPID;
        $data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $time = time();
        $data["timestamp"] = "$time";
        $data["noncestr"] = "1234568";
        $data["accesstoken"] = $getData["access_token"];
        ksort($data);
        $params = $this->ToUrlParams($data);
        $addrSign = sha1($params);

        $afterData = array(
            "addrSign" => $addrSign,
            "signType" => "sha1",
            "scope" => "jsapi_address",
            "appId" => WxPayConfig::APPID,
            "timeStamp" => $data["timestamp"],
            "nonceStr" => $data["noncestr"]
        );
        $parameters = json_encode($afterData);
        return $parameters;
    }
    /**
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = WxPayConfig::APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    /**
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = WxPayConfig::APPID;
        $urlObj["secret"] = WxPayConfig::APPSECRET;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * 设置微信分配的公众账号ID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['appId'] = $value;
    }
    /**
     * 获取微信分配的公众账号ID的值
     * @return 值
     **/
    public function GetAppid()
    {
        return $this->values['appId'];
    }
    /**
     * 判断微信分配的公众账号ID是否存在
     * @return true 或 false
     **/
    public function IsAppidSet()
    {
        return array_key_exists('appId', $this->values);
    }


    /**
     * 设置支付时间戳
     * @param string $value
     **/
    public function SetTimeStamp($value)
    {
        $this->values['timeStamp'] = $value;
    }
    /**
     * 获取支付时间戳的值
     * @return 值
     **/
    public function GetTimeStamp()
    {
        return $this->values['timeStamp'];
    }
    /**
     * 判断支付时间戳是否存在
     * @return true 或 false
     **/
    public function IsTimeStampSet()
    {
        return array_key_exists('timeStamp', $this->values);
    }

    /**
     * 随机字符串
     * @param string $value
     **/
    public function SetNonceStr($value)
    {
        $this->values['nonceStr'] = $value;
    }
    /**
     * 获取notify随机字符串值
     * @return 值
     **/
    public function GetReturn_code()
    {
        return $this->values['nonceStr'];
    }
    /**
     * 判断随机字符串是否存在
     * @return true 或 false
     **/
    public function IsReturn_codeSet()
    {
        return array_key_exists('nonceStr', $this->values);
    }
    /**
     * 设置订单详情扩展字符串
     * @param string $value
     **/
    public function SetPackage($value)
    {
        $this->values['package'] = $value;
    }
    /**
     * 获取订单详情扩展字符串的值
     * @return 值
     **/
    public function GetPackage()
    {
        return $this->values['package'];
    }
    /**
     * 判断订单详情扩展字符串是否存在
     * @return true 或 false
     **/
    public function IsPackageSet()
    {
        return array_key_exists('package', $this->values);
    }

    /**
     * 设置签名方式
     * @param string $value
     **/
    public function SetSignType($value)
    {
        $this->values['signType'] = $value;
    }
    /**
     * 获取签名方式
     * @return 值
     **/
    public function GetSignType()
    {
        return $this->values['signType'];
    }
    /**
     * 判断签名方式是否存在
     * @return true 或 false
     **/
    public function IsSignTypeSet()
    {
        return array_key_exists('signType', $this->values);
    }

    /**
     * 设置签名方式
     * @param string $value
     **/
    public function SetPaySign($value)
    {
        $this->values['paySign'] = $value;
    }
    /**
     * 获取签名方式
     * @return 值
     **/
    public function GetPaySign()
    {
        return $this->values['paySign'];
    }
    /**
     * 判断签名方式是否存在
     * @return true 或 false
     **/
    public function IsPaySignSet()
    {
        return array_key_exists('paySign', $this->values);
    }
}

