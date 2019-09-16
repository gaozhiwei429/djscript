<?php
/**
 * 子订单模型
 * @文件名称: OrderDetailModel.php
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
class OrderDetailModel extends BaseModel {
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
        return '{{%order_detail}}';
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
            $thisModel->user_id = isset($addData['user_id']) ? intval($addData['user_id']) : 0;//评论者用户id
            $thisModel->order_detail_no = isset($addData['order_detail_no']) ? trim($addData['order_detail_no']) : ""; //子订单编号
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::DEFAULT_STATUS;
            $thisModel->source = isset($addData['source']) ? trim($addData['source']) : date('Y-m-d H:i:s',strtotime('+1year')); //订单过期时间
            $thisModel->project_id = isset($addData['project_id']) ? intval($addData['project_id']) : 0; //评论对象的id标识
            $thisModel->is_pay_invoice = isset($addData['is_pay_invoice']) ? intval($addData['is_pay_invoice']) : 1;//是否支付发票【0未开，1已开】
            $thisModel->buy_number = isset($addData['buy_number']) ? intval($addData['buy_number']) : 1;
            $thisModel->order_id = isset($addData['order_id']) ? intval($addData['order_id']) : 0;
            $thisModel->price = isset($addData['price']) ? floatval($addData['price']) : 0;//实际产品支付金额
            $thisModel->total_amount = isset($addData['total_amount']) ? floatval($addData['total_amount']) : $thisModel->buy_number*$thisModel->price;
            $thisModel->old_price = isset($addData['old_price']) ? floatval($addData['old_price']) : 0;
            $thisModel->new_price = isset($addData['new_price']) ? floatval($addData['new_price']) : 0;
            $thisModel->title = isset($addData['title']) ? trim($addData['title']) : "";
            $thisModel->type_id = isset($addData['type_id']) ? intval($addData['type_id']) : 0;
            $thisModel->pay_from = isset($addData['pay_from']) ? intval($addData['pay_from']) : 0;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_order_detail_model_error', $e);
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
            DmpLog::error('update_order_detail_model_error', $e);
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
            DmpLog::error('insert_order_detail_model_error', $e);
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
            DmpLog::warning('getListDataByOrderIds_order_detail_model_error', $e);
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
            DmpLog::warning('getListData_order_detail_model_error', $e);
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
            DmpLog::warning('getCount_order_detail_model_error', $e);
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
            DmpLog::error('getinfo_order_detail_model_error', $e);
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
}
