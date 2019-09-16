<?php
/**
 * 环境配置相关
 * @文件名称: EvnUtil.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace app\components;
class EvnUtil
{
    /**
     * 是否为dev环境
     *
     * @return bool
     */
    public static function isDevEvn()
    {
        return defined('YII_ENV') && (YII_ENV === 'dev');
    }


    /**
     * 是否为beta环境
     *
     * @return bool
     */
    public static function isBetaEvn()
    {
        return defined('YII_ENV') && (YII_ENV === 'beta');
    }

    /**
     * 是否为prod环境
     *
     * @return bool
     */
    public static function isProdEvn()
    {
        return defined('YII_ENV') && (YII_ENV === 'prod');
    }

    /**
     * 是否为本地环境
     */
    public static function isLocalEvn()
    {
        return defined('YII_ENV') && (YII_ENV == 'local');
    }
}
