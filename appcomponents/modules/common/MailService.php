<?php

/**
 * 邮箱相关的service
 * @文件名称: MailService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use source\manager\BaseService;
use Yii;
class MailService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\common\controllers';
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }
    /**
     * 发送邮件
     * @param $sendTo 发送给
     * @param string $title 标题
     * @param string $htmlTmp 模板
     * @param array $params 参数
     * @param string $content 邮件内容
     * @return array
     */
    public function send($sendTo, $title="瑞安科技", $htmlTmp="", $params=[], $content="") {
        if($htmlTmp) {
            $params['sendTo'] = $sendTo;
            $mail= Yii::$app->mailer->compose($htmlTmp,$params);
        } else {
            $mail= Yii::$app->mailer->compose();
        }
        $mail->setTo($sendTo);
        $mail->setSubject($title."温馨提示！");
        if(empty($htmlTmp)) {
            $mail->setTextBody($content);   //发布纯文字文本
        }
        if($mail->send())
            return BaseService::returnOkData($sendTo);
        else
            return BaseService::returnErrData([]);
    }
}
