<?php
/**
 * 图片处理
 * @文件名称: DmpImage.php
 * @author: jawei
 * @Email: gaozhiwei@etcp.cn
 * @Date: 2017-06-06
 * @Copyright: 2017 悦畅科技有限公司. All rights reserved.
 * 注意：本内容仅限于悦畅科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */

namespace app\components;

use app\services\jdb\UPYun;
use yii\imagine\Imagine\Gd\Imagine;
use yii\imagine\Imagine\Image\Box;
use yii\imagine\Imagine\Image\ImageInterface;
use yii\imagine\Imagine\Image\Point;

/**
 * http://www.yiichina.com/tutorial/378
 * http://www.yiiframework.com/doc-2.0/ext-imagine-index.html
 * http://imagine.readthedocs.io/en/latest/
 *
 * http://www.68dl.com/bigdata_tech/2015/0228/19990.html 这个`可能`对图片处理做到更好, 但是有安全隐患
 *
 * Class DmpImage
 * @package app\components
 */
class DmpImage
{
    /** @var array 默认的保存参数 */
    private static $defaultOptions = [
        'jpeg' => [
            'jpeg_quality' => 0,
        ],
        'jpg' => [
            'jpeg_quality' => 0,
        ],
        'png'  => [
            'png_compression_level' => 0,
        ],
    ];

    private static $commonOptions = [
        'flatten' => false,
    ];

    /**
     * 投资有道文章迁移: 获取, 裁剪, 上传, 获得新生成图片的UPYun url.
     *
     * 图片: 长*宽
     *
     * NEW:
     * banner 750 * 300
     * normal 160 * 120
     * share  120 * 120
     *
     * OLD:
     * banner 640 * 290
     * share  120 * 120
     *
     * Usage:
     *  $url = 'http://jdbopt.b0.upaiyun.com/upload/interf/reward/20160616144038891.jpg';
     *  $urlData = DmpImage::getFormatUrlForInvestArticlesImmigration($url);
     *
     * @param $url string UPYun image url
     * @return array bannerUrl & normalUrl
     */
    public static function getFormatUrlForInvestArticlesImmigration($url)
    {
        $resize = [
            'banner' => [
                'width'  => 750,
                'height' => 300,
            ],
            'normal' => [
                'width'  => 160,
                'height' => 120,
            ],
        ];

        $imagine = new Imagine();
        $image   = $imagine->open($url);

        switch (DmpUtil::getType($url)) {
            case 'png':
                $option = self::$defaultOptions['png'];
                break;
            case 'jpeg':
                $option = self::$defaultOptions['jpeg'];
                break;
            case 'jpg':
                $option = self::$defaultOptions['jpg'];
                break;
            default:
                $option = [];
        }

        $option = array_merge($option, self::$commonOptions);

        $image->resize(new Box($resize['banner']['width'], $resize['banner']['height']), ImageInterface::FILTER_UNDEFINED);
        $savePath = \Yii::$app->params['projectRoot'] . '/upload/banner_' . DmpUtil::getUrlName($url);
        $image->save($savePath, $option);
        $bannerUrl = UPYun::uploadForLocal($savePath);

        $image->resize(new Box($resize['normal']['width'], $resize['normal']['height']), ImageInterface::FILTER_UNDEFINED);
        $savePath = \Yii::$app->params['projectRoot'] . '/upload/normal_' . DmpUtil::getUrlName($url);
        $image->save($savePath, $option);
        $normalUrl = UPYun::uploadForLocal($savePath);

        return [
            'banner' => $bannerUrl,
            'normal' => $normalUrl
        ];
    }

    /**
     * 通用修改url图片格式
     *
     * @param $url
     * @param $resizeWidth
     * @param $resizeHeight
     * @return string
     */
    public static function getFormatUrl($url, $resizeWidth, $resizeHeight)
    {
        $imagine = new Imagine();
        $image = $imagine->open($url);

        switch (DmpUtil::getType($url)) {
            case 'png':
                $option = self::$defaultOptions['png'];
                break;
            case 'jpeg':
                $option = self::$defaultOptions['jpeg'];
                break;
            case 'jpg':
                $option = self::$defaultOptions['jpg'];
                break;
            default:
                $option = [];
        }

        $option = array_merge($option, self::$commonOptions);
        $localPath = self::getSaveLocalPath($url);

        $image->resize(new Box($resizeWidth, $resizeHeight), ImageInterface::FILTER_UNDEFINED);
        $image->save($localPath, $option);

        return UPYun::uploadForLocal($localPath);
    }

    public static function outboundFormatUrl($url, $resizeWidth, $resizeHeight)
    {
        $imagine = new Imagine();
        $localPath = self::getSaveLocalPath($url);
        $imagine->open($url)
            ->thumbnail(new Box($resizeWidth, $resizeHeight), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save($localPath);

        return UPYun::uploadForLocal($localPath);
    }

    /**
     * 使小图裁剪放缩, 获得更好的图片
     *
     * h0, w0: 需求图片的大小, h1, w1: 上传图片大小
     * Resize: h1/w1 * max{h0/h1, w0/w1}
     * If max ~ w: cut w from center.
     *
     * @param $url
     * @param $requireWidth
     * @param $requireHeight
     * @return string
     */
    public static function makeSmallImageBetter($url, $requireWidth, $requireHeight)
    {
        $imagine = new Imagine();
        $image = $imagine->open($url);

        $originBox = $image->getSize();
        $imageWidth = $originBox->getWidth();
        $imageHeight = $originBox->getHeight();

        $ratio = max(round($requireHeight / $imageHeight, 5), round($requireWidth / $imageWidth, 5));

        $resizeWidth = ceil($imageWidth * $ratio);
        $resizeHeight = ceil($imageHeight * $ratio);

        $resizeUrl = self::getFormatUrl($url, $resizeWidth, $resizeHeight);

        $cutUrl = self::cutFromCenter($resizeUrl, $requireWidth, $requireHeight);

        return $cutUrl;
    }

    /**
     * 现在是大图只把边缘裁剪掉.
     *
     * 该方法是先进行等比缩小, 再居中裁剪. 尽可能多的还原图片原貌.
     *
     * @param $url
     * @param $requireWidth
     * @param $requireHeight
     * @return string
     */
    public static function makeBigImageBetter($url, $requireWidth, $requireHeight)
    {
        $imagine = new Imagine();
        $image = $imagine->open($url);

        $originBox = $image->getSize();
        $imageWidth = $originBox->getWidth();
        $imageHeight = $originBox->getHeight();

        $ratio = max(round($requireHeight / $imageHeight, 5), round($requireWidth / $imageWidth, 5));

        $resizeWidth = ceil($imageWidth * $ratio);
        $resizeHeight = ceil($imageHeight * $ratio);

        $resizeUrl = self::getFormatUrl($url, $resizeWidth, $resizeHeight);

        $cutUrl = self::cutFromCenter($resizeUrl, $requireWidth, $requireHeight);

        return $cutUrl;
    }

    /**
     * 存储图片url的本地路径
     *
     * @param $url
     * @return string
     */
    private static function getSaveLocalPath($url)
    {
        return \Yii::$app->params['projectRoot'] . '/upload/' . date('YmdHis') . DmpUtil::getUrlName($url);
    }

    /**
     * 按中心裁剪图片, 上传到UPYun, 返回图片url
     *
     * @param $url string 图片url
     * @param $cutSizeWidth int 裁剪的宽
     * @param $cutSizeHeight int 裁剪的高
     * @return string
     */
    public static function cutFromCenter($url, $cutSizeWidth, $cutSizeHeight)
    {
        $localName = \Yii::$app->params['projectRoot'] . '/upload/' . date('YmdHis') . md5(DmpUtil::getUrlName($url)) . '.' . DmpUtil::getType($url);

        $imagine = new Imagine();
        $image = $imagine->open($url);

        $originBox = $image->getSize();

        $pointStart = [
            'width' => max(ceil(($originBox->getWidth() - $cutSizeWidth) / 2), 0),
            'height' => max(ceil(($originBox->getHeight() - $cutSizeHeight) / 2), 0)
        ];

        $image->crop(new Point($pointStart['width'], $pointStart['height']),
            new Box($cutSizeWidth, $cutSizeHeight))
            ->save($localName);

        $uploadUrl = UPYun::uploadForLocal($localName);

        system('rm ' . $localName); // 删掉新生成到本地的图片

        return $uploadUrl;
    }

    public static function getBetterImage($url, $width, $height)
    {
        $imagine = new Imagine();
        $image = $imagine->open($url);

        $originBox = $image->getSize();

        /** 如果格式相同, 则不裁剪 */
        if (intval($originBox->getWidth()) == $width && intval($originBox->getHeight()) == $height) {
            return $url;
        }

        /** 如果图片过小, 则放缩 */
        if (intval($originBox->getWidth()) < $width || intval($originBox->getHeight()) < $height) {
            return self::makeSmallImageBetter($url, $width, $height);
        }

        if (intval($originBox->getWidth()) > $width || intval($originBox->getHeight()) > $height) {
            return self::makeBigImageBetter($url, $width, $height);
        }

        /** 如果图片两边都大, 则直接居中裁剪 */
        return self::cutFromCenter($url, $width, $height);
    }
}
