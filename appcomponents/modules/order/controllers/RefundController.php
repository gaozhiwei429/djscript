<?php
/**
 * 申请退款相关接口请求入口操作
 * @文件名称: RefundController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\order\controllers;
use appcomponents\modules\order\RefundService;
use source\controllers\UserBaseController;
use source\libs\DmpLog;
use source\manager\BaseService;
use Yii;

class RefundController extends UserBaseController
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
     * 用户申请退款提交
     * @return array
     */
    public function actionApply() {
        DmpLog::debug(Yii::$app->request->post());
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        //提交数据到购物车
        $order_id = intval(Yii::$app->request->post('order_id', 0));
        $content = trim(Yii::$app->request->post('content', "用户主动申请订单退款"));
        $describe = trim(Yii::$app->request->post('describe', ""));
        $picUrlArr = Yii::$app->request->post('pic_url', []);
        $type = intval(Yii::$app->request->post('type', 1));//退货退款【1、仅退款 2退货退款】
        $logistics_no = trim(Yii::$app->request->post('logistics_no', ""));//物流单号
        $logistics_name = trim(Yii::$app->request->post('logistics_name', ""));//物流公司
        $logistics_mobile = trim(Yii::$app->request->post('logistics_mobile', ""));//物流电话
        if(!is_array($picUrlArr)) {
            $picUrlArr = json_decode($picUrlArr,true);
        }
        if($order_id<=0) {
            return BaseService::returnErrData([], 53900, "请求参数异常");
        }
        $refundService = new RefundService();
        return $refundService->apply($this->user_id, $order_id, $content, $describe, $picUrlArr,$type,$logistics_no, $logistics_name, $logistics_mobile);
    }
    /**
     *取消退款
     */
    public function actionCannelApply() {
        if(!isset($this->user_id) || !$this->user_id) {
            return BaseService::returnErrData('', 5001, "登陆状态已失效");
        }
        $refund_id = intval(Yii::$app->request->post('refund_id', 0));
        $refundService = new RefundService();
        return $refundService->cannelApply($this->user_id, $refund_id);
    }
}
