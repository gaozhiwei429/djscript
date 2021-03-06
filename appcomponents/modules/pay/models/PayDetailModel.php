<?php
/**
 * 子支付模型
 * @文件名称: PayDetailModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-17
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\pay\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;
class PayDetailModel extends BaseModel {
    //状态【-1删除，0已取消，10待支付，20已支付，30已发货,40已过期，50已申请退款，60退款中，70已退款】
    const DELETE_STATUS = -1;
    const CANCEL_STATUS = 0;
    const DEFAULT_STATUS = 10;
    const PAY_STATUS = 20;
    const ALREADY_DELIVER_STATUS = 30;
    const OVERDUE_STATUS = 40;
    const APPLY_REFUND_STATUS = 50;
    const REFUND_STATUS = 60;
    const ALREADY_REFUND_STATUS = 70;

    const PAY_WCHAT_SOURCE = 2;//微信支付
    const PAY_ALI_SOURCE = 1;//支付宝支付
    //是否支付发票【0未开，1已开】
    const NO_PAY_INVOICE = 0;
    const ALREADY_PAY_INVOICE = 1;
    public static function tableName() {
        return '{{%pay_detail}}';
    }
    /**
     * 可以支付的状态
     * @return array
     */
    public function getCanPayStatus() {
        return [
            self::DELETE_STATUS,
            self::OVERDUE_STATUS,
        ];
    }
    /**
     * 不可以支付的状态
     * @return array
     */
    public function getNotCanPayStatus() {
        return [
            self::PAY_STATUS,
            self::ALREADY_DELIVER_STATUS,
            self::APPLY_REFUND_STATUS,
            self::REFUND_STATUS,
            self::ALREADY_REFUND_STATUS,
        ];
    }
    /**
     * 根据条件获取最后一条信息
     * @param $verify_value
     * @param int $type
     * @return mixed
     */
    public function getInfoByParams($params){
        return $this->getOne($params);
    }
    /**
     * 添加一条评论记录表
     * @param $addData
     * @return bool|string
     */
    public function addInfo($addData) {
        try {
            $thisModel = new self();
            $thisModel->id = isset($addData['id']) ? trim($addData['id']) : 0;
            $thisModel->receipt_amount = isset($addData['receipt_amount']) ? floatval($addData['receipt_amount']) : 0;//现金支付总金额
            $thisModel->buyer_id = isset($addData['buyer_id']) ? trim($addData['buyer_id']) : "";//买家第三方账号对应的唯一用户号
            $thisModel->trade_no = isset($addData['trade_no']) ? trim($addData['trade_no']) : "";//第三方支付流水号
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::DEFAULT_STATUS;
            $thisModel->callbak_time = isset($addData['callbak_time']) ? trim($addData['callbak_time']) : "";//支付回调时间
            $thisModel->order_id = isset($addData['order_id']) ? intval($addData['order_id']) : 0;//订单唯一标识
            $thisModel->pay_id = isset($addData['pay_id']) ? intval($addData['pay_id']) : 0;//主支付记录id
            $thisModel->pay_type = isset($addData['pay_type']) ? intval($addData['pay_type']) : 0;//支付方式【1支付宝，2微信】
            $thisModel->pay_from = isset($addData['pay_from']) ? intval($addData['pay_from']) : 1;//支付来源如【1APP，2Web，3微信公众号，4线下】
            $thisModel->app_id = isset($addData['app_id']) ? intval($addData['app_id']) : 0;//平台的商户号
            $thisModel->callbak_result = isset($addData['callbak_result']) ? trim($addData['callbak_result']) : ""; //Callbak->content回调返回的完整结果数据
            $thisModel->title = isset($addData['title']) ? trim($addData['title']) : ""; //冗余的商家服务名称：商品名称
            $thisModel->my_trade_no = isset($addData['my_trade_no']) ? trim($addData['my_trade_no']) : ""; //平台支付流水号
            $thisModel->user_id = isset($addData['user_id']) ? intval($addData['user_id']) : 0;//提交者用户id
            $thisModel->total_amount = isset($addData['total_amount']) ? floatval($addData['total_amount']) : 0;//支付总金额
            $thisModel->coupon_amount = isset($addData['coupon_amount']) ? floatval($addData['coupon_amount']) : 0;//代金券金额
            $thisModel->coupon_count = isset($addData['coupon_count']) ? intval($addData['coupon_count']) : 0;//代金券使用数量
            $thisModel->coupon_ids = isset($addData['coupon_ids']) ? trim($addData['coupon_ids']) : "";//代金券ID数组
            $thisModel->coupon_fees = isset($addData['coupon_fees']) ? trim($addData['coupon_fees']) : "";//代金券支付金额数组
            $thisModel->my_coupon_ids = isset($addData['my_coupon_ids']) ? trim($addData['my_coupon_ids']) : "";//平台的抵扣券ID
            $thisModel->my_coupon_fees = isset($addData['my_coupon_fees']) ? trim($addData['my_coupon_fees']) : "";//平台的单个抵扣券支付金额
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_pay_detail_model_error', $e);
            return false;
        }
    }
    /**
     * 更新信息数据
     * @param int $id ID
     * @param array $updateInfo 需要更新的数据集合
     * @return bool
     */
    public static function updateInfo($id, $updateInfo) {
        try {
            $datainfo = self::findOne(['id' => $id]);
            if(!empty($updateInfo)) {
                foreach($updateInfo as $k=>$v) {
                    $datainfo->$k = trim($v);
                }
                return $datainfo->save();
            }
            return false;
        } catch (BaseException $e) {
            DmpLog::error('update_pay_detail_model_error', $e);
            return false;
        }
    }

    /**
     * 添加一条记录到记录表
     * @param $addData
     * @return bool|string
     */
    public function addData($data) {
        try {
            if(empty($data)) {
                return false;
            }
            $thisModel = new self();
            foreach($data as $k=>$v) {
                $thisModel->$k = $v;
            }
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_pay_detail_model_error', $e);
            return false;
        }
    }
    /**
     * 获取数据集
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @param boole $index是否将主键作为key
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDatas($params = [], $orderBy = [], $offset = 0, $limit = 100, $fied=['*'], $orWhereParams = [], $index=false) {
        $query = self::find()->select($fied);
        $where = ['and'];
        if(!empty($params)) {
            foreach($params as $k=>$v) {
                if(is_array($v)) {
                    $where[] = $v;
//                    $query -> andWhere($v);
                } else {
                    $where[] = [$k=>$v];
//                    $query -> andWhere([$k=>$v]);
                }
            }
        }
        if(!empty($orWhereParams)) {
            $orwhere = ['or'];
            foreach($orWhereParams as $k=>$v) {
                if(is_array($v)) {
                    $orwhere[] = $v;
                } else {
                    $orwhere = [$k=>$v];
                }
            }
            $where[] = $orwhere;
        }
        $query -> where($where);
        if ($limit !== -1) {
            $query -> offset($offset);
            $query -> limit($limit);
        }
        if (!empty($orderBy)) {
            $query -> orderBy($orderBy);
        }
        $projectList = $query->asArray()->all();

        $projectListArr = [];
        if($index) {
            foreach($projectList as $projectInfo) {
                if(isset($projectInfo['id']) && $projectInfo['id']) {
                    $projectListArr[$projectInfo['id']] = $projectInfo;
                }
            }
            if(!empty($projectListArr)) {
                return $projectListArr;
            }
        }
//                var_dump($query->createCommand()->getRawSql());die;
        //        return $query->createCommand()->getRawSql();
        return $projectList;
    }
    /**
     * 根据条件获取数据集
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @param boole $orderIdindex是否将order_id作为key
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDatasByOrderIds($params, $orderBy = [], $offset = 0, $limit = 100, $fied=['*'], $orWhereParams = [], $orderIdindex=false) {
        $query = self::find()->select($fied);
        $where = ['and'];
        if(!empty($params)) {
            foreach($params as $k=>$v) {
                if(is_array($v)) {
                    $where[] = $v;
//                    $query -> andWhere($v);
                } else {
                    $where[] = [$k=>$v];
//                    $query -> andWhere([$k=>$v]);
                }
            }
        }
        if(!empty($orWhereParams)) {
            $orwhere = ['or'];
            foreach($orWhereParams as $k=>$v) {
                if(is_array($v)) {
                    $orwhere[] = $v;
                } else {
                    $orwhere = [$k=>$v];
                }
            }
            $where[] = $orwhere;
        }
        $query -> where($where);
        if ($limit !== -1) {
            $query -> offset($offset);
            $query -> limit($limit);
        }
        if (!empty($orderBy)) {
            $query -> orderBy($orderBy);
        }
        $projectList = $query->asArray()->all();

        $projectListArr = [];
        if($orderIdindex) {
            foreach($projectList as $projectInfo) {
                if(isset($projectInfo['order_id']) && $projectInfo['order_id']) {
                    $projectListArr[$projectInfo['order_id']] = $projectInfo;
                }
            }
            if(!empty($projectListArr)) {
                return $projectListArr;
            }
        }
//                var_dump($query->createCommand()->getRawSql());die;
        //        return $query->createCommand()->getRawSql();
        return $projectList;
    }
    /**
     * 获取分页数据列表
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @param boole $orderIdindex是否将order_id作为key
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListDataByOrderIds($orderIdArr, $orderBy = [], $offset = 0, $limit = 10, $fied=['*'], $orWhereParams=[], $orderIdindex=true) {
        try {
            $params[] = ['in', 'order_id', $orderIdArr];
            $dataList = self::getDatasByOrderIds($params, $orderBy, $offset, $limit, $fied, $orWhereParams, $orderIdindex);
            $data = [
                'dataList' => $dataList,
                'count' => 0,
            ];
            if(!empty($dataList)) {
                $count = self::getCount($params, $orWhereParams);
                $data['count'] = $count;
            }
            return $data;
        } catch (BaseException $e) {
            DmpLog::warning('getListDataByOrderIds_pay_detail_model_error', $e);
            $data = [
                'dataList' => [],
                'count' => 0,
            ];
            return $data;
        }
    }
    /**
     * 获取分页数据列表
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @param boole $index是否将主键作为key
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListData($params = [], $orderBy = [], $offset = 0, $limit = 10, $fied=['*'], $orWhereParams=[], $index=false) {
        try {
            $dataList = self::getDatas($params, $orderBy, $offset, $limit, $fied, $orWhereParams, $index);
            $data = [
                'dataList' => $dataList,
                'count' => 0,
            ];
            if(!empty($dataList)) {
                $count = self::getCount($params, $orWhereParams);
                $data['count'] = $count;
            }
            return $data;
        } catch (BaseException $e) {
            DmpLog::warning('getListData_pay_detail_model_error', $e);
            $data = [
                'dataList' => [],
                'count' => 0,
            ];
            return $data;
        }
    }
    /**
     * 获取总数量
     * @param $params
     * @return int
     */
    public static function getCount($whereParams, $orWhereParams, $fied=['*']) {
        try {
            $query = self::find()->select($fied);
            $where = ['and'];
            if(!empty($whereParams)) {
                foreach($whereParams as $k=>$v) {
                    if(is_array($v)) {
                        $where[] = $v;
//                    $query -> andWhere($v);
                    } else {
                        $where[] = [$k=>$v];
//                    $query -> andWhere([$k=>$v]);
                    }
                }
            }
            if(!empty($orWhereParams)) {
                $orwhere = ['or'];
                foreach($orWhereParams as $k=>$v) {
                    if(is_array($v)) {
                        $orwhere[] = $v;
                    } else {
                        $orwhere = [$k=>$v];
                    }
                }
                $where[] = $orwhere;
            }
            $query -> where($where);
//                return $query->createCommand()->getRawSql();
            return  $query->count();
        } catch (BaseException $e) {
            DmpLog::warning('getCount_pay_detail_model_error', $e);
            return 0;
        }
    }
    /**
     * 通过id获取基本信息数据
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getInfoById($id) {
        try {
            $datainfo = self::findIdentity($id);
            return $datainfo;
        } catch (BaseException $e) {
            DmpLog::error('getinfo_pay_detail_model_error', $e);
            return [];
        }
    }
    /**
     * 更新数据
     * @param $where
     * @param $updateData
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateAllDataList($columns, $condition = '') {
        return Yii::$app->db->createCommand()->update(self::tableName(), $columns, $condition)->execute();
    }
    /**
     * 批量更新数据
     * @param $where
     * @param $updateData
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateAllDataListByParams($whereColumns, $updateData) {
        if (count($whereColumns) == 0 || !is_array($whereColumns) || empty($updateData)) {
            return false;
        }
        $sql = '';
        $where = [];
        $update = [];
        foreach ($whereColumns as $key => $value) {
            if(is_array($value) && !empty($value)) {
                $where[] = " `$key` in (".implode(',', $value).") ";
            } else {
                $where[] = " `$key`='$value' ";
            }
        }
        foreach($updateData as $key => $value) {
            if(is_array($value) && !empty($value)) {
                return false;
            } else {
                $update[] = " `$key`='$value' ";
            }
        }
        $sql .= 'UPDATE '.self::tableName().' SET '.implode(',',$update).' WHERE ' . implode(' and ', $where);
//        DmpLog::debug($sql);
        $result = Yii::$app->db->createCommand($sql)->execute();
        return $result;
    }
    /**
     * 批量添加记录数据
     * @param $user_id
     * @param $files
     * @return int
     * @throws \yii\db\Exception
     */
    public function addAll($datas) {
        $data = [];
        $clumns = (isset($datas[0]) && !empty($datas[0])) ? array_keys($datas[0]) : [];
        if(empty($clumns)) {
            return false;
        }
        foreach ($datas as $k => $v) {
            $data[] = $v;
        }
        return Yii::$app->db->createCommand()->batchInsert(self::tableName(), $clumns, $data)->execute();
    }

    /**
     * 更新信息数据
     * @param int $user_id 用户ID
     * @param array $updateInfo 用户需要更新的数据集合
     * @return bool
     */
    public function updateDataInfoByParams($params, $updateInfo) {
        try {
            $datainfo = $this->getInfoByParams($params);
            if(!empty($updateInfo)) {
                foreach($updateInfo as $k=>$v) {
                    $datainfo->$k = trim($v);
                }
                return $datainfo->save();
            }
            return false;
        } catch (BaseException $e) {
            DmpLog::warning('update_user_info_model_exception', $e);
            return false;
        }
    }
}
