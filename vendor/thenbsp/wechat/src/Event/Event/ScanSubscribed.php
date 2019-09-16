<?php

namespace Thenbsp\Wechat\Event\Event;

use Thenbsp\Wechat\Event\Event;

class ScanSubscribed extends Event
{
    /**
     * 扫描带参数的二维码时，提交参数格式为 "SCENE_参数值"，
     * 为了方便获取参数值，手动添加了一个参数叫 "EventKeyValue"
     * 该参数仅在 EventScanSubscribe 和 EventScanSubscribed 事件中可用.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('SCAN' === $this['Event']);
    }
}
