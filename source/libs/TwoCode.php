<?php
/*************************************************************************
 * File Name :    app/models/Qrcode.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/
namespace source\libs;
use source\manager\BaseService;
use Yii;
/**
 * 生成二维码
 **/
class TwoCode
{
    /**
     * 生成二维码
     *
     * @params  array   msg 中包含的是图片的地址
     **/
    public function create($test, $level = 'M', $pointSize = 5, $prefix = 'png') {
        include dirname(__FILE__)."/phpqrcode/phpqrcode.php";
        $qrcodeDir = Yii::$app->params['upload']['qrcode'];
        $webHost = Yii::$app->params['project']['host'];
        if(!file_exists($qrcodeDir)){
            mkdir($qrcodeDir, 0777, true);
        }
        $file = $qrcodeDir . md5($test).".".$prefix;
        if (file_exists($file)) {
            return BaseService::returnOkData($file);
        } else {
            //生成二维码图片
            \QRcode::png($test, $file, $level, $pointSize, 2);
            $webHostFile = $webHost . trim($file,".");
            if (file_exists($file)) {
                return BaseService::returnOkData($webHostFile);
            }
            return BaseService::returnErrData($webHostFile, 53800, '生成文件失败');
        }
    }
    /**
     * 生成加logo图片的二维码
     * @param $test
     * @param string $level
     * @param int $pointSize
     * @param string $prefix
     * @return array
     */
    public function createLogonQrcode($test, $level = 'H', $pointSize = 10, $prefix = 'png') {
        include dirname(__FILE__)."/phpqrcode/phpqrcode.php";
        $qrcodeDir = Yii::$app->params['upload']['qrcode'];
        $webHost = Yii::$app->params['project']['host'];
        if(!file_exists($qrcodeDir)){
            mkdir($qrcodeDir, 0777, true);
        }
        $file = $qrcodeDir . md5($test).".".$prefix;
        if (file_exists($file)) {
            return BaseService::returnOkData($file);
        } else {
            $file = $qrcodeDir . md5($test).".".$prefix;
            if (!file_exists($file)) {
                $filename = $qrcodeDir . md5($test).".".$prefix;
                \QRcode::png($test, $filename, $level, $pointSize, 2);
                $QR = $filename;
                $logo = Yii::$app->basePath."/"."web/logo.png";
                if ($logo !== FALSE) {
                    $QR = imagecreatefromstring(file_get_contents($QR));
                    $logo = imagecreatefromstring(file_get_contents($logo));
                    if (imageistruecolor($logo)) imagetruecolortopalette($logo, false, 65535);

                    $QR_width = imagesx($QR);//二维码图片宽度
                    $QR_height = imagesy($QR);//二维码图片高度
                    $logo_width = imagesx($logo);//logo图片宽度
                    $logo_height = imagesy($logo);//logo图片高度
                    $logo_qr_width = $QR_width / 2.5;
                    $scale = $logo_width/$logo_qr_width;
                    $logo_qr_height = $logo_height/$scale;
                    $from_width = ($QR_width - $logo_qr_width) / 2;
                    //重新组合图片并调整大小
                    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                        $logo_qr_height, $logo_width, $logo_height);
                }
                //display generated file
                imagepng($QR, $filename);
                $webHostFile = $webHost . trim($filename,".");
                if (file_exists($file)) {
                    return BaseService::returnOkData($webHostFile);
                }
            }
            $webHostFile = $webHost . trim($file,".");
            return BaseService::returnErrData($webHostFile, 53800, '生成文件失败');
        }
    }
}
