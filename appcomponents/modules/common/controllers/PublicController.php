<?php

namespace appcomponents\modules\common\controllers;
use source\controllers\BaseController;
use source\libs\WxPay\lib\Jssdk;
use source\libs\WxPay\lib\WxPayConfig;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;

class PublicController extends BaseController
{
    /**
     * 获取微信分享参数
     * @return array
     */
    public function actionWxShare() {
        try {
            $app_id = WxPayConfig::APPID;
            $appSecret = WxPayConfig::APPSECRET;
            $url = trim(Yii::$app->request->post('url', gethostname()));
//            var_dump($url);die;
            $jsSdk = new Jssdk($app_id, $appSecret);
            $signPackage = $jsSdk->GetSignPackage($url);
//            var_dump($signPackage);die;
            return BaseService::returnOkData($signPackage);
        } catch(BaseException $e) {
            return BaseService::returnErrData([], 500, "获取失败");
        }
    }
}
