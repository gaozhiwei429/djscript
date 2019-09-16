<?php

/**
 * Validator验证类
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use yii\web\Request;

/**
 * UrlValidator validates that the attribute value is a valid http or https URL.
 *
 * Note that this validator only checks if the URL scheme and host part are correct.
 * It does not check the rest part of a URL.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequestValidator {
	public static $globalParams = null;
	public $attributes = [ 
			'os_type',
			'user_id',
			'token',
			'time_stamp',
			'app_key',

	];
	
	/**
	 * @inheritdoc
	 */
	public function validate() {
		// 此处实现请求的合法性校验，true代表请求合法
		// 校验参数
		if (! Yii::$app->params ['enable_sign']) {
			return true;
		}
		
		$requestPost = Yii::$app->request->post ();
		foreach ( $requestPost as $key => $postValue ) {
			if (is_array ( $postValue )) {
				foreach ( $postValue as $subKey => $subValue ) {
					if (is_array ( $subValue )) {
						foreach ( $subValue as $indexKey => $detailValue ) {
							$requestPost [$key . '[' . $subKey . ']' . '[' . $indexKey . ']'] = $detailValue;
						}
					} else {
						$requestPost [$key . '[' . $subKey . ']'] = $subValue;
					}
				}
				unset ( $requestPost [$key] );
			}
		}
		$validateParams = array_merge ( $requestPost, self::$globalParams );
		foreach ( $validateParams as $key => $param ) {
			if ($param == null || $param == '') {
				unset ( $validateParams [$key] );
			}
		}
		$signSubmit = $validateParams ['sign'];
		unset ( $validateParams ['sign'] );
		ksort ( $validateParams );
		
		$validateParams ['secret'] = Yii::$app->params ['app_secret'];
		
		$signStringArray = [ ];
		foreach ( $validateParams as $key => $validate ) {
			$signStringArray [] = $key . '=' . $validate;
		}
		$signOfRequest = sha1 ( implode ( '&', $signStringArray ) );
		
		if ($signSubmit === $signOfRequest) {
			return true;
		} else {
			$stringOfSign = implode ( '&', $signStringArray );
			throw new Exception ( 'sign is not correct, request is not legal!', 4034 );
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function load() {
		$headParams = Yii::$app->request->getHeaders ()->toArray ();
		if (! isset ( $headParams ['inwatch-base'] ) || (! is_array ( $headParams ['inwatch-base'] )) || empty ( $headParams ['inwatch-base'] )) {
			if (! Yii::$app->params ['enable_sign']) {
				return $this;
			} else {
				throw new Exception ( 'Request is not legal!,inwatch-base can not be found', 400 );
			}
		}
		$appHeadParams = json_decode ( array_shift ( $headParams ['inwatch-base'] ), true );
		foreach ( $this->attributes as $attribute ) {
			if (isset ( $appHeadParams [$attribute] )) {
				self::$globalParams [$attribute] = $appHeadParams [$attribute];
			} else {
				self::$globalParams [$attribute] = '';
			}
		}
		return $this;
	}
	public function getParams() {
		return self::$globalParams;
	}
}
