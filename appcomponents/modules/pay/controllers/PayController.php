<?php
/**
 * 支付相关接口请求入口操作
 * @文件名称: UserController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\pay\controllers;
use appcomponents\modules\common\CommonService;
use ixiaomu\payment\Pay;
use JiaLeo\Payment\Paypal\AppPay;
use JiaLeo\Payment\Wechatpay\H5Pay;
use JiaLeo\Payment\Wechatpay\MpPay;
use JiaLeo\Payment\Wechatpay\NativePay;
use source\controllers\UserBaseController;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
use yii\base\Exception;

class PayController extends UserBaseController
{
    /**
     * 用户登录态基础类验证
     * @return array
     */
    public function beforeAction($action){
        $userToken = $this->userToken();
        return parent::beforeAction($action);
    }
    /**
     * 支付结算记录支付
     * @return array
     */
    public function actionPay() {
        $config = Yii::$app->params['pay']['wechat'];
        $return_params['order_id'] = time();
        $data = [];
        $data['device']='mp';
        $data['body']='党费缴纳';
        $data['amount']=5;
        $openid = 'ofKvvtwQjleAMZKr7cvT6Jwcjavw';
        if ($data['device'] == 'mp') {
            $wechatpay = new MpPay($config);
        } elseif ($data['device'] == 'app') {
            $wechatpay = new AppPay($config);
        }
        elseif($data['device'] == 'h5'){
            $wechatpay = new H5Pay($config);
        }
        elseif ($data['device'] == 'native') {
            $wechatpay = new NativePay($config);
        }

        $out_trade_no = date('YmdHis') . rand(10000, 99999);
        $pay_data = [
            'body' => $data['body'], //内容
            'attach' => $wechatpay->setPassbackParams($return_params), //商家数据包
            'out_trade_no' => $out_trade_no, //商户订单号
            'total_fee' => $data['amount'], //支付价格(单位:分)
            'openid' => $openid,
            'notify_url' => 'http://domain/api/wechatpay/notifies/' . $data['device'] //后台回调地址
        ];
        if ($data['device'] == 'mp' || $data['device'] == 'app') {
            var_dump($wechatpay);die;
        }elseif($data['device'] == 'h5'){
            $url = $wechatpay->handle($pay_data);
            header("Content-Type: text/html; charset=utf-8");
            $str="<a href=\"$url\">107网站工作室</a>";
            echo $str;
            echo "<br>";
        }elseif ($data['device'] == 'native') {
            echo "输出二维码";
        }
        die;
        echo htmlentities($str,ENT_QUOTES,"UTF-8");
//        DmpLog::debug(Yii::$app->request->post());
//        if(!isset($this->user_id) || !$this->user_id) {
//            return BaseService::returnErrData('', 5001, "登陆状态已失效");
//        }
        /*$payData = [
            'out_trade_no'     => time(), // 订单号
            'total_fee'        => '520000', // 订单金额，**单位：分**
            'body'             => '订单描述', // 订单描述
            'openid'           => 'ofKvvtwQjleAMZKr7cvT6Jwcjavw', // 支付人的 openID
        ];
        $payConfig = Yii::$app->params['pay']['wechat'];
        $pay = new Pay($payConfig);
        try{
            $redult = $pay->driver('wechat')->gateway('mp')->apply($payData);
//            var_dump($redult);die;
        }catch (Exception $e){
            throw new Exception('支付失败：'.$e->getMessage());
        }
        return BaseService::returnErrData([], 516000, "支付方式未知");*/
    }
}
