<?php

/**
 * 退款申请相关的service
 * @文件名称: SmsService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\order;
use appcomponents\modules\order\models\OrderDetailModel;
use appcomponents\modules\order\models\OrderHandModel;
use appcomponents\modules\order\models\OrderModel;
use appcomponents\modules\order\models\RefundModel;
use appcomponents\modules\pay\models\PayDetailModel;
use appcomponents\modules\pay\PayService;
use appcomponents\modules\project\ProjectService;
use source\libs\Common;
use source\libs\DmpLog;
use source\manager\BaseService;
use Yii;
class RefundService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\order\controllers';
    public function init()
    {
        parent::init();
    }
    /**
     * C端退款记录数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $refundModel = new RefundModel();
        $refundList = $refundModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($refundList)) {
            return BaseService::returnOkData($refundList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }

    /**
     * 申请退款接口
     * @param $user_id
     * @param $order_id
     * @param string $content
     * @param string $describe
     * @param string $picUrlArr
     * @param int $type
     * @param string $logistics_no
     * @param string $logistics_name
     * @param string $logistics_mobile
     * @return array
     * @throws \yii\db\Exception
     */
    public function apply($user_id, $order_id, $content="", $describe="", $picUrlArr="",$type=1,$logistics_no="", $logistics_name="", $logistics_mobile="") {
        if($order_id <= 0) {
            return BaseService::returnErrData([], 55400, "请求参数异常");
        }
        $orderService = new OrderService();
        $payService = new PayService();
        //开启事务处理
        if($content) {
            $addOrderHandData['content'] = $content;
            $addOrderHandData['type'] = OrderHandModel::TYPE_REFUND;
            $addOrderHandData['order_id'] = $order_id;
            $addOrderHandData['user_id'] = $user_id;
            $addOrderHandData['describe'] = $describe;
            $addOrderHandData['pic_url'] = is_array($picUrlArr) ? json_encode($picUrlArr) : json_encode([$picUrlArr]);
            $orderService->addOrderHandData($addOrderHandData);
        }
        $orderParams[] = ['=', 'id', $order_id];
        $payParams[] = ['=', 'order_id', $order_id];
        $payInfoRet = $payService->getDetailInfo($payParams);
        $orderInfoRet = $orderService->getOrderInfoByParams($orderParams);
        if(!BaseService::checkRetIsOk($payInfoRet)) {
            return BaseService::returnErrData([], 57300, "该订单未支付不能申请退款");
        }
        if(BaseService::checkRetIsOk($orderInfoRet) && BaseService::checkRetIsOk($payInfoRet)) {
            $orderInfo = BaseService::getRetData($orderInfoRet);
            $payInfo = BaseService::getRetData($payInfoRet);
            $status = isset($orderInfo['status']) ? $orderInfo['status'] : 0;
            $canAppyRefundOrderStatus = $orderService->getCanAppyRefundOrderStatus();
            if(in_array($status, $canAppyRefundOrderStatus)) {
                $tr = Yii::$app->db->beginTransaction();
                $refundData['refund_no'] = $this->createRefundNo();
                $refundData['order_id'] = $order_id;
                $refundData['refund_type'] = isset($orderInfo['pay_type']) ? $orderInfo['pay_type'] : 0;
                $refundData['user_id'] = isset($orderInfo['user_id']) ? $orderInfo['user_id'] : 0;
                $refundData['refund_amount'] = isset($orderInfo['total_amount']) ? floatval($orderInfo['total_amount']) : 0;
                $refundData['buyer_id'] = isset($payInfo['buyer_id']) ? trim($payInfo['buyer_id']) : "";
                $refundData['reason'] = $content;
                $refundData['type'] = intval($type);//退货退款【1、仅退款 2退货退款】
                $refundData['logistics_no'] = trim($logistics_no);//物流单号
                $refundData['logistics_name'] = trim($logistics_name);//物流公司
                $refundData['logistics_mobile'] = trim($logistics_mobile);//物流电话
                $addRefundRet = $this->addData($refundData);//添加退款数据
                //更新支付详情状态
                $payDetailParams['order_id'] = $order_id;
                $updatePayDetail['status'] = PayDetailModel::APPLY_REFUND_STATUS;
                $payRet = $payService->updateDetailInfoByParams($payDetailParams, $updatePayDetail);
                //更新订单状态
                $orderUpdateData['status'] = OrderModel::APPLY_REFUND_STATUS;
                $orderUpdateRet = $orderService->updateData($order_id, $orderUpdateData);
                if(BaseService::checkRetIsOk($addRefundRet)
                    && BaseService::checkRetIsOk($payRet)
                    && BaseService::checkRetIsOk($orderUpdateRet)
                ){
                    $tr->commit();
                    return BaseService::returnOkData([]);
                }else{
                    $tr->rollBack();
                }
            }
        }
        if($logistics_no && $type==2) {
            $refundData['type'] = intval($type);//退货退款【1、仅退款 2退货退款】
            $refundData['logistics_no'] = trim($logistics_no);//物流单号
            $refundData['logistics_name'] = trim($logistics_name);//物流公司
            $refundData['logistics_mobile'] = trim($logistics_mobile);//物流电话
            $params[] = ['=', 'order_id', $order_id];
            $ret = $this->getInfoByParams($params);
            if(BaseService::checkRetIsOk($ret)) {
                $retData = BaseService::getRetData($ret);
                $id = isset($retData['id']) ? $retData['id'] : 0;
                $status = isset($retData['status']) ? $retData['status'] : 0;
                if($status == OrderModel::APPLY_REFUND_STATUS) {
                    return $this->updateData($id, $refundData);
                }
                return BaseService::returnOkData([], 513900, "该订单状态未提交申请退款");
            }
            return $ret;
        }
        return BaseService::returnErrData([], 58000, "申请失败");
    }
    /**
     * 获取可以申请退款的状态
     * @return array
     */
    public function getCanApplyStatus() {
        return [
            RefundModel::DEFAULT_STATUS,
            RefundModel::CANCEL_STATUS,
        ];
    }
    /**
     * 取消退款申请
     * @param $user_id
     * @param $refund_id
     * @return array
     */
    public function cannelApply($user_id, $refund_id) {
        if($refund_id <= 0) {
            return BaseService::returnErrData([], 516300, "请求参数异常");
        }
        $params[] = ['=', 'user_id', $user_id];
        $params[] = ['=', 'id', $refund_id];
        $refundInfoRet = $this->getInfoByParams($params);
        if(BaseService::checkRetIsOk($refundInfoRet)) {
            $refundInfo = BaseService::getRetData($refundInfoRet);
            if(isset($refundInfo['order_id']) && !$refundInfo['order_id']) {
                return BaseService::returnErrData([], 517200, "没有可申请的退款订单");
            }
            if(isset($refundInfo['status']) && !in_array($refundInfo['status'],$this->getCanApplyStatus())) {
                return BaseService::returnErrData([], 516900, "该状态不可操作");
            }
            $tr = Yii::$app->db->beginTransaction();
            $order_id = $refundInfo['order_id'];
            $refundData['status'] = RefundModel::CANCEL_STATUS;
            $updateRefundRet = $this->updateData($refund_id, $refundData);
            $orderService = new OrderService();
            $orderData['status'] = OrderModel::PAY_STATUS;
            $updateOrderRet = $orderService->updateData($order_id, $orderData);
            $payService = new PayService();
            $payParams[] = ['=', 'order_id', $order_id];
            $payDetailInfoRet = $payService->getPayDetailInfoByParams($payParams);
            if(BaseService::checkRetIsOk($payDetailInfoRet)) {
                $payDetailInfo = BaseService::getRetData($payDetailInfoRet);
                $payDetailId = isset($payDetailInfo['id']) ? $payDetailInfo['id'] : 0;
                $payData['status'] = PayDetailModel::PAY_STATUS;
                $updatePayDetailRet = $payService->updatePayInfoById($payDetailId, $payData);
                if(BaseService::checkRetIsOk($updateRefundRet)
                    && BaseService::checkRetIsOk($updateOrderRet)
                    && BaseService::checkRetIsOk($updatePayDetailRet)
                ){
                    $tr->commit();
                    return BaseService::returnOkData([]);
                }else{
                    $tr->rollBack();
                }
            }
        }
        return BaseService::returnErrData([], 519600, "申请失败");
    }
    /**
     * 退款申请数据添加
     * @param $data
     * @return array
     */
    public function addData($data) {
        if(!empty($data) && is_array($data)) {
            $refundModel = new RefundModel();
            $ret = $refundModel->addData($data);
            if($ret) {
                return BaseService::returnOkData($ret);
            }
            return BaseService::returnErrData([], 58100, "提交退款申请异常");
        }
        return BaseService::returnErrData([], 512500, "添加退款数据失败");
    }
    /**
     * 获取退款详情记录数据
     * @param $params
     * @return array
     */
    public function getInfoByParams($params) {
        if(!empty($params) && is_array($params)) {
            $refundModel = new RefundModel();
            $refundInfo = $refundModel->getInfoByParams($params);
            if(!empty($refundInfo)) {
//                $overdue_time = strtotime($refundInfo['create_time'])+24*60*60*2;
//                $refundInfo['overdue_time_des'] = date('Y-m-d H:i:s', $overdue_time);
                return BaseService::returnOkData($refundInfo);
            }
        }
        return BaseService::returnErrData([], 512500, "当前退款数据不存在");
    }
    /**
     * 【申请退款单号（32位）】 时间（system.currentTimeMillis() 13位）+随机数6位
     * 生成订单号规则
     * @return string
     */
    public function createRefundNo($length=32) {
        $commonObj = new Common();
        $refund_no = $commonObj->createLongNumberNo($length);
        $params[] = ['=', 'refund_no', $refund_no];
        $ret = $this->getInfoByParams($params);
        if(!BaseService::checkRetIsOk($ret)) {
            return $refund_no;
        }
        return $this->createRefundNo($length);
    }
    /**
     * 更新数据
     * @param $id
     * @param $updateData
     * @return array
     */
    public function updateData($id, $updateData) {
        $refundModel = new RefundModel();
        $updatePrinter = $refundModel->updateInfo($id, $updateData);
        if($updatePrinter) {
            return BaseService::returnOkData($updatePrinter);
        }
        return BaseService::returnErrData($updatePrinter,537500, "更新失败");
    }
}
