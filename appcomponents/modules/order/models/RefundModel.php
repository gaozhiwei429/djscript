<?php
/**
 *退款记录表模型
 * @文件名称: RefundModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-17
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\order\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;
class RefundModel extends BaseModel {
    //退款状态【-1取消，0未退款，10退款成功，20退款失败】
    const DEFAULT_STATUS = 0;
    const SUCCESS_STATUS = 10;
    const CANCEL_STATUS = -1;
    const FAIL_STATUS = 20;
    //退款方式【1支付宝，2微信】
    const REFUND_ALI_TYPE = 1;
    const REFUND_WCHAT_TYPE = 2;
    public static function tableName() {
        return '{{%refund}}';
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
            $thisModel->refund_no = isset($addData['refund_no']) ? trim($addData['refund_no']) : "";//退款单号
            $thisModel->order_id = isset($addData['order_id']) ? intval($addData['order_id']) : 0;//主订单id
            $thisModel->refund_type = isset($addData['type']) ? intval($addData['type']) : self::REFUND_ALI_TYPE; //原因【1取消，2退款，3失效】
            $thisModel->refund_time = isset($addData['refund_time']) ? trim($addData['refund_time']) : "";//退款日期
            $thisModel->trade_no = isset($addData['trade_no']) ? trim($addData['trade_no']) : "";//退款交易号
            $thisModel->user_id = isset($addData['user_id']) ? intval($addData['user_id']) : self::DEFAULT_STATUS;//支付用户id
            $thisModel->refund_amount = isset($addData['refund_amount']) ? floatval($addData['refund_amount']) : 0;//退款总金额
            $thisModel->refund_receipt_amount = isset($addData['refund_receipt_amount']) ? floatval($addData['refund_receipt_amount']) : 0;//实际退款金额
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::DEFAULT_STATUS;//退款状态【0未退款，10退款成功，20退款失败】
            $thisModel->buyer_id = isset($addData['buyer_id']) ? trim($addData['buyer_id']) : "";//买家第三方账号对应的第三方唯一用户号
            $thisModel->reason = isset($addData['reason']) ? trim($addData['reason']) : "";//用户提交的退款说明
            $thisModel->coupon_count = isset($addData['coupon_count']) ? intval($addData['coupon_count']) : 0;//实际退款代金券使用数量
            $thisModel->coupon_amount = isset($addData['coupon_amount']) ? floatval($addData['coupon_amount']) : 0;//代金券金额
            $thisModel->coupon_ids = isset($addData['coupon_ids']) ? trim($addData['coupon_ids']) : "";//实际退款代金券ID数组
            $thisModel->coupon_fees = isset($addData['coupon_fees']) ? trim($addData['coupon_fees']) : "";//实际退款代金券支付金额数组
            $thisModel->receipt_amount = isset($addData['receipt_amount']) ? floatval($addData['receipt_amount']) : 0;//实际支付现金金额
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_refund_model_error', $e);
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
            DmpLog::error('update_refund_model_error', $e);
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
            DmpLog::error('insert_refund_model_error', $e);
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
        return $projectList;
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
            DmpLog::warning('getListData_refund_model_error', $e);
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
            DmpLog::warning('getCount_refund_model_error', $e);
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
            DmpLog::error('getinfo_refund_model_error', $e);
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
//    public function updateAllDataList($columns, $condition = '') {
        //return Yii::$app->db->createCommand()->update(self::tableName(), $columns, $condition)->execute();
//    }
    /**
     * 批量更新数据
     * @param $where
     * @param $updateData
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateAllDataList($whereColumns, $updateData) {
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
        $result = Yii::$app->db->createCommand($sql)->execute();
        return $result;
    }
}
