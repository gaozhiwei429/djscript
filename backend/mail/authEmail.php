<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = '星驰恒动';
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['passport/user/auth-email', 'token' => $token]);
?>
<p><b><?=$this->title;?>提醒您</b></p>
<p>尊敬的:<b><?=$username;?></b></p>
<p>您的账户绑定邮箱为:<?=$sendTo;?>，认证链接如下：</p>
<p><a href="<?=$resetLink;?>"><?=$resetLink;?></a></p>
<p>该链接5分钟内有效，请勿传递给别人</p>
<p>该邮件为系统自动发送，请勿回复！！</p>