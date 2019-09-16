<?php
namespace source\libs\WxPay\lib;
use source\libs\DmpLog;
use source\libs\DmpRedis;
use Yii;
/**
 * 
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * JSSDK
 * @author jawei
 * @email gaozhiwei429@sina.com
 */
class WxJSSDK{
    private $appId;
    private $appSecret;
    private $jsapiTicket;
    private $DmpRedis;
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->DmpRedis = new DmpRedis();
//        $jsapiTicket = isset(Yii::$app->params['rediskey']['weixin']['jsapiTicket']) ?
//            Yii::$app->params['rediskey']['weixin']['jsapiTicket'] : '';
//        $this->jsapiTicket = $this->DmpRedis->get($jsapiTicket) ? ;
    }


    public function getSignPackage($urlStr = '') {
        $jsapiTicket = $this->getJsApiTicket();
        if(empty($urlStr)){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }else{
            $url = $urlStr;
        }
        // 注意 URL 一定要动态获取，不能 hardcode.

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->getJsApiJsapiTicket());
        if ((isset($data->expire_time) && $data->expire_time < time()) ||
            (isset($data->jsapi_ticket) && $data->jsapi_ticket == '')
        ) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = isset($res->ticket) ? $res->ticket : "";
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->setJsapiTicket(json_encode($data), 7000);
            } else {
                $ticket = $data->jsapi_ticket;
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->setJsapiTicket(json_encode($data), 7000);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }
    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
//        $data = json_decode($this->get_php_file("./access_token.php"));
        $data = json_decode($this->getJsapiAccessToken());

        if ((isset($data->expire_time) && $data->expire_time < time()) ||
            (isset($data->access_token) && $data->access_token == '')
        ) {
//        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            DmpLog::debug($url);
            DmpLog::debug($this->httpGet($url));
            $access_token = isset($res->access_token) ? $res->access_token : "";

            DmpLog::debug("access_token");
            DmpLog::debug($access_token);
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->setAccessToken(json_encode($data), 7000);
            } else {
                $access_token = isset($data->access_token) ? $data->access_token : "";
                if ($access_token) {
                    $data->expire_time = time() + 7000;
                    $data->access_token = $access_token;
                    $this->setAccessToken(json_encode($data), 7000);
                }
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function getJsapiAccessToken() {
        $jsapiTicket = isset(Yii::$app->params['rediskey']['weixin']['jsapiAccessToken']) ?
            Yii::$app->params['rediskey']['weixin']['jsapiAccessToken'] : '';
        $jsapiTicket = $this->DmpRedis->get($jsapiTicket);
        $ret = !empty($jsapiTicket) ? $jsapiTicket : '{"access_token":"","expire_time":0}';
        return trim(substr($ret, 0));
    }

    private function getJsApiJsapiTicket() {
        $jsapiTicket = isset(Yii::$app->params['rediskey']['weixin']['jsapiTicket']) ?
            Yii::$app->params['rediskey']['weixin']['jsapiTicket'] : '';
        $jsapiTicket = $this->DmpRedis->get($jsapiTicket);
        $ret = !empty($jsapiTicket) ? $jsapiTicket : '{"jsapi_ticket":"","expire_time":0}';
        return trim(substr($ret, 0));
    }

    private function setAccessToken($data, $expireTime=7200) {
        $jsapiTicket = isset(Yii::$app->params['rediskey']['weixin']['jsapiAccessToken']) ?
            Yii::$app->params['rediskey']['weixin']['jsapiAccessToken'] : '';
        $this->DmpRedis->set($jsapiTicket, $data);
        $this->DmpRedis->expire($jsapiTicket, $expireTime);
        return $data;
    }

    private function setJsapiTicket($data, $expireTime=7200) {
        $jsapiTicket = isset(Yii::$app->params['rediskey']['weixin']['jsapiTicket']) ?
            Yii::$app->params['rediskey']['weixin']['jsapiTicket'] : '';
        $this->DmpRedis->set($jsapiTicket, $data);
        $this->DmpRedis->expire($jsapiTicket, $expireTime);
        return $data;
    }
}
