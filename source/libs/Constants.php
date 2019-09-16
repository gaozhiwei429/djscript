<?php
/**
 * 系统常量定义
 * @文件名称: Constants.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */

namespace app\components;

class Constants
{
    /**
     * 通用删除状态: 0:有效; 1:已删除
     */
    const DELETE_STATUS_DEFAULT = 0;
    const DELETE_STATUS_DELETE = 1;

    /** dmp_tags 表中标签的可用状态 */
    const TAG_STATUS_USE = 1;
    const TAG_STATUS_NOT_USE = 0;

    public static $tagStatusMap = [
        self::TAG_STATUS_USE => '可用',
        self::TAG_STATUS_NOT_USE => '不可用'
    ];

    public static $TagNames =[
    ];
}
