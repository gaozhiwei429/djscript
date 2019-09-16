<?php
/**
 * 
 * 微信支付API异常类
 * @author jawei
 * @email gaozhiwei429@sina.com
 */
namespace source\libs\WxPay\lib;
class WxPayException extends \Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
