<?php
/**
 * CURL请求
 * @文件名称: DmpCurl.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */

namespace source\libs;
use source\manager\BaseException;
use Yii;

class DmpCurl
{
    public static function httpQuery($url, $param, $level='info') {
        if(!empty($param)) {
            $params_string = http_build_query($param);
            $ch = curl_init($url.'?'.$params_string);
        } else {
            $ch = curl_init($url);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        $contents = curl_exec($ch);
        $result = json_decode($contents,true) ? json_decode($contents,true) : $contents;
        $logInfo = [
            'url' => $url,
            'params' => $param,
            'result' => $result,
        ];
        DmpLog::info("http_query_info", $logInfo);
        return $result;
    }

    /**
     * x-www-form-urlencode 请求
     * @param $url
     * @param $param
     * @return mixed
     */
    public static function httpFormUrlEncodeQuery($url, $param) {
        $params_string = http_build_query($param);
        $ch = curl_init($url.'?'.$params_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencode'));
        $contents = curl_exec($ch);
        $result = json_decode($contents,true);
        $logInfo = [
            'url' => $url,
            'params' => $param,
            'result' => $result,
        ];
        DmpLog::info("httpFormUrlEncodeQuery", $logInfo);
        return $result;
    }
    public static function post($url, $content)
    {
        return self::basePost($url, $content, true);
    }

    public static function fastPost($url, $content)
    {
        return self::basePost($url, $content, false);
    }

    protected static function basePost($url, $content, $needLog)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER  => false,
            CURLOPT_CONNECTTIMEOUT => 50, // timeout on connect
            CURLOPT_TIMEOUT => 100, // timeout on response
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ];

        $ch = curl_init($url);
        if ($ch) {
            curl_setopt_array($ch, $options);
            $data = curl_exec($ch);
            curl_close($ch);
            if($needLog){
                $logInfo = [
                    'url' => $url,
                    'params' => $content,
                    'result' => $data
                ];
                DmpLog::info('curl_post_result',$logInfo);
            }
        } else {
            throw new BaseException('curl post fail: ' . curl_error($ch), 50031);
        }
        return $data;
    }
}
