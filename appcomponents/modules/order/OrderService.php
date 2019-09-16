<?php

/**
 * 短信相关的service
 * @文件名称: SmsService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\order;
use appcomponents\modules\common\ProcessServiceService;
use appcomponents\modules\my\UserProjectService;
use appcomponents\modules\order\models\OrderDetailModel;
use appcomponents\modules\order\models\OrderHandModel;
use appcomponents\modules\order\models\OrderModel;
use appcomponents\modules\project\ProjectService;
use source\libs\Common;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\manager\BaseService;
use Yii;
class OrderService extends BaseService
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
     * C端主订单记录数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $orderModel = new OrderModel();
        $orderList = $orderModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($orderList)) {
            return BaseService::returnOkData($orderList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * C端主订单数据总数获取
     * @param $addData
     * @return array
     */
    public function getCount($params = []) {
        $orderModel = new OrderModel();
        $orderNum = $orderModel->getCount($params, []);
        if(!empty($orderNum)) {
            return BaseService::returnOkData($orderNum);
        }
        return BaseService::returnOkData(0);
    }
    /**
     * 提交数据到购物车
     * @param $user_id
     * @param $project_id
     * @param int $buy_number
     * @param int $pay_from
     * @param int $pay_type 支付方式【1支付宝，2微信，3银联】
     * @param int $type 订单类型【1普通产品订单、2续租相关的订单、3加工服务相关的订单】
     * @param int $renewal_id 续租记录id
     * @return array
     */
    public function addGoodsCar($user_id, $project_id, $buy_number=1, $pay_from=1, $pay_type=0, $type=1, $renewal_id=0) {
        $orderModel = new OrderModel();
        //获取当前用户的产品再购物车里面是否存在，如果存在那么就追加购买数量即可
        $goodsCarParams = [];
        if($project_id) {
            $goodsCarParams[] = ['=', 'project_id', $project_id];
            $goodsCarParams[] = ['=', 'user_id', $user_id];
            $goodsCarParams[] = ['=', 'status', $orderModel::DEFAULT_STATUS];
            $goodsCarParams[] = ['=', 'type', $type];
            $goodsCarRet = $this->getOrderInfoByParams($goodsCarParams);
            if(BaseService::checkRetIsOk($goodsCarRet)) {
                $goodsCarInfo = BaseService::getRetData($goodsCarRet);
                $id = isset($goodsCarInfo['id']) ? $goodsCarInfo['id'] : 0;
                $have_buy_number = isset($goodsCarInfo['buy_number']) ? $goodsCarInfo['buy_number'] : 0;
                if($id && $have_buy_number) {
                    $rest = $this->editBuyNumber($user_id, $id, $buy_number+$have_buy_number);
                    if(BaseService::checkRetIsOk($rest)) {
                        return BaseService::returnOkData($id);
                    }
                }
            }
        }
        $addData = [];
        if($type==1) {
            if(empty($user_id) || empty($project_id) || $buy_number<=0|| $pay_from<=0) {
                return BaseService::returnErrData([], 55000, "请求参数异常");
            }
        }

        $addData['renewal_id'] = $renewal_id;
        $addData['type'] = $type;
        if($user_id) {
            $addData['user_id'] = $user_id;
        }
        $orderNo = $this->createOrderNo(19);
        if($orderNo) {
            $addData['order_no'] = $orderNo;
        }
        $overdue_time = isset(Yii::$app->params['order']['overdue_time']) ? Yii::$app->params['order']['overdue_time'] : 30;//单位分钟
        if($overdue_time) {
            $overdue_time = time()+$overdue_time*60;
            $addData['overdue_time'] = date('Y-m-d H:i:s', $overdue_time);
        }
        $stock_num = 0;
        $type_id = 0;
        if($type==1 && $project_id) {
//            if ($project_id) {
                $projectParams = [];
                $projectParams[] = ['=', 'id', $project_id];
                $projectService = new ProjectService();
                $projectInfoRet = $projectService->getInfo($projectParams);
                if (BaseService::checkRetIsOk($projectInfoRet)) {
                    $projectInfo = BaseService::getRetData($projectInfoRet);
                    if (isset($projectInfo['status']) && $projectInfo['status'] != 1) {
                        return BaseService::returnErrData([], "当前产品已下线，不可购买");
                    }
                    if (isset($projectInfo['stock_num']) && $projectInfo['stock_num'] != 0) {
                        $stock_num = $projectInfo['stock_num'];
                    }
                    if($stock_num < $buy_number) {
                        return BaseService::returnErrData([], 511400, "当前库存不足，请联系客服");
                    }
                    if (isset($projectInfo['price'])) {
                        $addData['old_price'] = floatval($projectInfo['price']);
                    }
                    if (isset($projectInfo['name'])) {
                        $addData['title'] = trim($projectInfo['name']);
                    }
                    if (isset($projectInfo['type_id'])) {
                        $type_id = $addData['type_id'] = intval($projectInfo['type_id']);
                    }
                    $addData['pay_type'] = $pay_type;
                    if (isset($projectInfo['now_price'])) {
                        $addData['price'] = $addData['now_price'] = floatval($projectInfo['now_price']);
                    }
                    $addData['project_id'] = $project_id;
                    $addData['total_amount'] = $buy_number * $addData['price'];
                } else {
                    return BaseService::returnErrData([], 512900, "你所购买产品不存在");
                }
//            } else {
//                return BaseService::returnErrData([], 513200, "您购买的产品不存在");
//            }
        } else if($type==2){
            $addData['buy_number'] = 1;
            if ($renewal_id) {
                $projectParams = [];
                $projectParams[] = ['=', 'id', $renewal_id];
                $projectService = new RenewalService();
                $projectInfoRet = $projectService->getInfoByParams($projectParams);
                if (BaseService::checkRetIsOk($projectInfoRet)) {
                    $projectInfo = BaseService::getRetData($projectInfoRet);
                    if (isset($projectInfo['status']) && $projectInfo['status'] > 10) {
                        return BaseService::returnErrData([], 514300, "当前状态不可支付");
                    }
                    if (isset($projectInfo['total_amount'])) {
                        $addData['price'] = $addData['now_price'] = $addData['old_price'] = floatval($projectInfo['total_amount']);
                    }
                    $addData['total_amount'] = $buy_number * $addData['price'];
                    $addData['title'] = "打印机续费";
                    $addData['pay_type'] = $pay_type;
                    $addData['project_id'] = 0;
                } else {
                    return BaseService::returnErrData([], 515700, "你所购买产品不存在");
                }
            }
            $addData['renewal_id'] = $renewal_id;
        } else if($type==3){
            $addData['buy_number'] = 1;
            if ($renewal_id) {
                $projectParams = [];
                $projectParams[] = ['=', 'id', $renewal_id];
                $projectService = new ProcessServiceService();
                $projectInfoRet = $projectService->getInfo($projectParams);
                if (BaseService::checkRetIsOk($projectInfoRet)) {
                    $projectInfo = BaseService::getRetData($projectInfoRet);
                    if (isset($projectInfo['status']) && $projectInfo['status'] == 3) {
                        return BaseService::returnErrData([], 518700, "当前状态不可支付");
                    }
                    if (isset($projectInfo['price'])) {
                        $addData['price'] = $addData['now_price'] = $addData['old_price'] = floatval($projectInfo['price']);
                    }
                    $addData['total_amount'] = $buy_number * $addData['price'];
                    $addData['title'] = "加工服务订单";
                    $addData['pay_type'] = $pay_type;
                    $addData['project_id'] = 0;
                } else {
                    return BaseService::returnErrData([], 515700, "您的加工提交记录不存在");
                }
            }
            $addData['process_service_id'] = $renewal_id;
        } else {
            return BaseService::returnErrData([], 516200, "你所购买产品不存在");
        }
        if($pay_from) {
            $addData['pay_from'] = $pay_from;
        }
        if(empty($addData) || (isset($addData['total_amount']) && $addData['total_amount']<=0)) {
            return BaseService::returnErrData([], 57100, "提交数据有误");
        }
        $addData['is_show'] = 1;
        $order_id = $orderModel->addInfo($addData);
        if($order_id) {
            //我的模型库数据
            $userProjectService = new UserProjectService();
            $userProjectService->addData($user_id, $project_id, $order_id, $type_id, 0);
            return BaseService::returnOkData($order_id);
        }
        return BaseService::returnErrData($order_id, 56600, "提交购物车失败");
    }
    /**
     * 根据主订单条件查询主订单表详情数据查询
     * @param $order_no
     * @return array
     */
    public function getOrderInfoByParams($params) {
        if(!empty($params) && is_array($params)) {
            $orderModel = new OrderModel();
            $orderInfo = $orderModel->getInfoByParams($params);
            if(!empty($orderInfo)) {
                return BaseService::returnOkData($orderInfo);
            }
        }
        return BaseService::returnErrData([], 514100, "当前订单数据不存在");
    }
    /**
     * 根据主订单编号查询主订单表详情数据查询
     * @param $order_no
     * @return array
     */
    public function getOrderInfoByOrderNo($order_no) {
        if(!empty($order_no)) {
            $orderModel = new OrderModel();
            $orderParams[] = ['=', 'order_no', $order_no];
            $orderInfo = $orderModel->getInfoByParams($orderParams);
            if(!empty($orderInfo)) {
                return BaseService::returnOkData($orderInfo);
            }
        }
        return BaseService::returnErrData([], 512500, "当前订单数据不存在");
    }
    /**
     * 【订单号（19位）】 时间（system.currentTimeMillis() 13位）+随机数6位
     * 生成订单号规则
     * @return string
     */
    public function createOrderNo($length=19) {
        $commonObj = new Common();
        $order_no = $commonObj->createLongNumberNo($length);
        $ret = $this->getOrderInfoByOrderNo($order_no);
        if(!BaseService::checkRetIsOk($ret)) {
            return $order_no;
        }
        return $this->createOrderNo($length);
    }
    /**
     * 主订单表可支付的状态
     * @return array
     */
    public function getOrderCanPayStatus() {
        $orderModel = new OrderModel();
        return $orderModel->getCanPayStatus();
    }
    /**
     * 主订单表部 可支付的状态
     * @return array
     */
    public function getOrderNotCanPayStatus() {
        $orderModel = new OrderModel();
        return $orderModel->getNotCanPayStatus();
    }
    /**
     * 获取主订单列表数据
     * @param array $params
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @return array
     */
    public function getOrderListByParams($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $orWhereParams=[], $index=true) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $orderModel = new OrderModel();
        $orderListData = $orderModel->getListData($params, $orderBy, $offset, $limit, $fied, $orWhereParams, $index);
        if(!empty($orderListData)) {
            return BaseService::returnOkData($orderListData);
        }
        return BaseService::returnErrData($orderListData, 517400, "数据不存在");
    }
    /**
     * 获取订单详情列表数据
     * @param array $params
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @return array
     */
    public function getOrderDetailListByParams($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $orWhereParams=[], $index=true) {
        $orderDetailModel = new OrderDetailModel();
        $orderDetailListData = $orderDetailModel->getListData($params, $orderBy, $p, $limit, $fied, $orWhereParams, $index);
        if(!empty($orderDetailListData)) {
            return BaseService::returnOkData($orderDetailListData);
        }
        return BaseService::returnErrData($orderDetailListData, 519000, "数据不存在");
    }
    /**
     * 获取订单详情列表数据
     * @param array $params
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @return array
     */
    public function getOrderDetailListByOrderIds($orderIdArr, $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $orWhereParams=[], $orderIdindex=true) {
        $orderDetailModel = new OrderDetailModel();
        $orderDetailListData = $orderDetailModel->getListDataByOrderIds($orderIdArr, $orderBy, $p, $limit, $fied, $orWhereParams, $orderIdindex);
        if(!empty($orderDetailListData)) {
            return BaseService::returnOkData($orderDetailListData);
        }
        return BaseService::returnErrData($orderDetailListData, 519000, "数据不存在");
    }
    /**
     * 提交订单验证是否可以支付
     * @param $userId
     * @param $orderIdArr
     * @param array $orderBy
     * @param int $p
     * @param int $limit
     * @param array $fied
     * @param array $orWhereParams
     * @return array
     */
    public function getDataArr($userId, $orderIdArr, $orderBy = [], $p = 1, $limit = 10, $fied=['*'], $orWhereParams=[]) {
        if(empty($orderIdArr) || !is_array($orderIdArr)) {
            return BaseService::returnErrData([], 522700, "参数异常");
        }
        $orderParams[] = ['in', 'id', $orderIdArr];
        $orderListDataRet = $this->getOrderListByParams($orderParams, $orderBy, $p, $limit, $fied, $orWhereParams, true);

        $orderIdArr = [];
        $totalAmountArr = [];
        $titleArr = [];
        $dataArr = [];
        if(BaseService::checkRetIsOk($orderListDataRet)) {
            $isCanPay = 1;//是否可以结算
            $canPayUserId = 1;//结算用户id
            $orderListData = BaseService::getRetData($orderListDataRet);
            if(isset($orderListData['dataList']) && !empty($orderListData['dataList'])) {
                foreach($orderListData['dataList'] as $dataInfo) {
                    if(isset($dataInfo['user_id']) && $dataInfo['user_id']!=$userId) {
                        $canPayUserId = 0;
                    }
                    if(isset($dataInfo['status']) && !in_array($dataInfo['status'], $this->getOrderCanPayStatus())) {
                        $isCanPay = 0;
                    }
                    if(isset($dataInfo['id'])) {
                        $orderIdArr[] = $dataInfo['id'];
                    }
                    if(isset($dataInfo['total_amount'])) {
                        $totalAmountArr[] = floatval($dataInfo['total_amount']);
                    }
                    if(isset($dataInfo['title'])) {
                        $titleArr[] = trim($dataInfo['title']);
                    }
                }
            }
            if($canPayUserId==0 || $isCanPay==0) {
                return BaseService::returnErrData([], 520000, "结算订单数据不存在或存在不可支付的订单");
            }
            $dataArr['titleArr'] = $titleArr;
            $dataArr['totalAmountArr'] = $totalAmountArr;
            $dataArr['orderIdArr'] = $orderIdArr;
            return BaseService::returnOkData($dataArr);
        }
        return BaseService::returnErrData([], 527000, "支付请求异常");
    }
    /**
     * 通过订单条件更新相关字段
     * @param $order_detail_id
     * @param $updateData
     * @return array
     */
    public function updateAllByParams($where, $updateData) {
        if((empty($where) || !is_array($where)) || (empty($updateData) || !is_array($updateData))) {
            return BaseService::returnErrData([], 530000, "请求参数异常");
        }
        $orderModel = new OrderModel();
        $ret = $orderModel->updateAllDataList($where, $updateData);
        if($ret) {
            return BaseService::returnOkData($ret);
        }
        return BaseService::returnErrData($ret, 530700, "更新数据异常");
    }
    /**
     * 取消绑定接口
     * @param $idArr
     * @return array
     */
    public function delData($user_id, $idArr) {
        if(empty($idArr) || !is_array($idArr)) {
            return BaseService::returnErrData([], 531600, "请求参数异常");
        }
        $idArr = array_unique($idArr);
        $params[] = ['=', 'user_id', $user_id];
        $params[] = ['in', 'id', $idArr];
        $params[] = ['!=', 'status', OrderModel::DELETE_STATUS];
        $listRet = $this->getList($params, [], 1, count($idArr));
        if(!BaseService::checkRetIsOk($listRet)) {
            return BaseService::returnErrData([], 516000, "当前请求数据不存在");
        }
        $listData = BaseService::getRetData($listRet);
        if(isset($listData['count']) && $listData['count']==count($idArr)) {
            $updateData['is_show'] = OrderModel::CAN_NOT_SHOW;
            $updateParams['id'] = $idArr;
            return $this->updateAllByParams($updateParams, $updateData);
        }
        return BaseService::returnErrData([], 517400, "删除失败");
    }
    /**
     * 添加订单的操作原因数据
     * @param $orderHandData
     * @return array
     */
    public function addOrderHandData($orderHandData) {
        $orderHandModel = new OrderHandModel();
        $addOrderHandData = $orderHandModel->addInfo($orderHandData);
        if($addOrderHandData) {
            return BaseService::returnOkData($addOrderHandData);
        }
        return BaseService::returnErrData($addOrderHandData, 534500, "添加数据失败");
    }
    /**
     * 更新数据
     * @param $id
     * @param $updateData
     * @return array
     */
    public function updateData($id, $updateData) {
        $orderModel = new OrderModel();
        $updatePrinter = $orderModel->updateInfo($id, $updateData);
        if($updatePrinter) {
            return BaseService::returnOkData($updatePrinter);
        }
        return BaseService::returnErrData($updatePrinter,537500, "更新失败");
    }
    /**
     * 修改购物车数量
     * @param $user_id
     * @param $id
     * @param int $buy_number
     * @return array
     */
    public function editBuyNumber($user_id, $id, $buy_number=1) {
        if($id>0) {
            $params[] = ['=', 'id', $id];
            $params[] = ['=', 'user_id', $user_id];
            $orderInfoRet = $this->getOrderInfoByParams($params);
            if(BaseService::checkRetIsOk($orderInfoRet)) {
                if($buy_number<=0) {
                    $buy_number =0;
//                    return $this->delData($user_id, [$id]);
                }
                $orderInfo = BaseService::getRetData($orderInfoRet);
                $now_price = isset($orderInfo['now_price']) ? $orderInfo['now_price'] : 0;
                $buyNumber = isset($orderInfo['buy_number']) ? $orderInfo['buy_number'] : 0;
                $status = isset($orderInfo['status']) ? $orderInfo['status'] : 0;
                if(!in_array($status, [OrderModel::DEFAULT_STATUS,OrderModel::READY_PAY_STATUS])) {
                    return BaseService::returnErrData([], 540100, "只支持购物车或待支付订单修改");
                }
                if($buyNumber == $buy_number) {
                    return BaseService::returnOkData($buy_number);
                }
                $update['buy_number'] = $buy_number;
                $update['is_show'] = 1;
                $update['total_amount'] = floatval($now_price*$buy_number);
                return $this->updateData($id, $update);
            }
        }
        return BaseService::returnErrData([], 510000, "请求参数异常");
    }
    /**
     * 取消订单
     * @param $user_id
     * @param $id
     * @param string $content
     * @param string $describe
     * @param string $picUrlArr
     * @return array
     */
    public function cancel($user_id, $id, $content="", $describe="", $picUrlArr="") {
        if($id>0) {
            //开启事务处理
            if($content) {
                $addOrderHandData['content'] = $content;
                $addOrderHandData['type'] = OrderHandModel::TYPE_CANCEL;
                $addOrderHandData['order_id'] = $id;
                $addOrderHandData['user_id'] = $user_id;
                $addOrderHandData['describe'] = $describe;
                $addOrderHandData['pic_url'] = is_array($picUrlArr) ? json_encode($picUrlArr) : json_encode([$picUrlArr]);
                $this->addOrderHandData($addOrderHandData);
            }
            $params[] = ['=', 'id', $id];
            $params[] = ['=', 'user_id', $user_id];
            $orderInfoRet = $this->getOrderInfoByParams($params);
            if(BaseService::checkRetIsOk($orderInfoRet)) {
                $orderInfo = BaseService::getRetData($orderInfoRet);
                $status = isset($orderInfo['status']) ? $orderInfo['status'] : 0;
                if($status == OrderModel::CANCEL_STATUS) {
                    return BaseService::returnOkData([]);
                }
                if($status != OrderModel::READY_PAY_STATUS) {
                    return BaseService::returnErrData([], 540100, "只支持待支付状态订单才能取消");
                }
                $update['status'] = OrderModel::CANCEL_STATUS;
                return $this->updateData($id, $update);
            }
        }
        return BaseService::returnErrData([], 510000, "请求参数异常");
    }
    /**
     * 确认签收订单接口
     * @param $user_id
     * @param $id
     * @return array
     */
    public function confirmReceipt($user_id, $id) {
        if($id>0) {
            $params[] = ['=', 'id', $id];
            $params[] = ['=', 'user_id', $user_id];
//            $params[] = ['in', 'status', [OrderModel::PAY_STATUS, OrderModel::ALREADY_DELIVER_STATUS]];
            $orderInfoRet = $this->getOrderInfoByParams($params);
            if(BaseService::checkRetIsOk($orderInfoRet)) {
                $orderInfo = BaseService::getRetData($orderInfoRet);
                $status = isset($orderInfo['status']) ? $orderInfo['status'] : 0;
                if(in_array($status,[OrderModel::PAY_STATUS, OrderModel::ALREADY_DELIVER_STATUS])) {
                    $update['status'] = OrderModel::ALREADY_SIGN_FOR;
                    return $this->updateData($id, $update);
                }
            }
            return BaseService::returnErrData([], 510000, "请求参数异常");
        }
        return BaseService::returnErrData([], 510000, "请求参数异常");
    }
    /**
     * 获取可申请发票的状态
     * @return array
     */
    public function getCanAppyInvoiceOrderStatus() {
        $OrderModel = new OrderModel();
        return [
            $OrderModel::PAY_STATUS,
            $OrderModel::ALREADY_DELIVER_STATUS,
            $OrderModel::ALREADY_SIGN_FOR,
        ];
    }
    /**
     * 获取可申请退款的状态
     * @return array
     */
    public function getCanAppyRefundOrderStatus() {
        $OrderModel = new OrderModel();
        return [
            $OrderModel::PAY_STATUS,
            $OrderModel::ALREADY_DELIVER_STATUS,
        ];
    }

    /**
     * 提醒发货
     * @param $user_id
     * @param $id
     * @param int $is_emind_send
     * @return array
     */
    public function remindSend($user_id, $id, $is_emind_send=1) {
        if($id>0) {
            $params[] = ['=', 'id', $id];
            $params[] = ['=', 'user_id', $user_id];
            $orderInfoRet = $this->getOrderInfoByParams($params);
            if(BaseService::checkRetIsOk($orderInfoRet)) {
                $orderInfo = BaseService::getRetData($orderInfoRet);
                if(!empty($orderInfo) && is_array($orderInfo)) {
                    $status = isset($orderInfo['status']) ? $orderInfo['status'] : 0;
                    if($status<OrderModel::PAY_STATUS) {
                        return BaseService::returnErrData([], 551900, "订单状态未支付不能操作");
                    }
                }
                if($is_emind_send<=0) {
                    $is_emind_send =0;
                }
                $update['is_emind_send'] = $is_emind_send;
                return $this->updateData($id, $update);
            }
            return $orderInfoRet;
        }
        return BaseService::returnErrData([], 510000, "请求参数异常");
    }
}
