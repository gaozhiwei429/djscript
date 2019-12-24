<?php
/**
 * 系统后台管理管理
 * Class Menu
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\models;
use source\libs\DmpLog;
use source\models\BaseModel;
use Yii;
class MenuModel extends BaseModel {
    //是否有效[1有效，2无效，0屏蔽
    const IS_STATUS = 1;
    const NO_STATUS = 2;
    const NOT_STATUS = 0;

    //是否被删除
    const IS_DEL = 1;
    const NO_DEL = 0;

    public static function tableName() {
        return '{{%menu}}';
    }
    /**
     * 状态数组
     * @return array
     */
    public function getStatus() {
        return [
            self::NOT_STATUS => '屏蔽',
            self::IS_STATUS => '有效',
            self::NO_STATUS => '无效',
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
     * 添加一条记录到记录表
     * @param $addData
     * @return bool|string
     */
    public function addInfo($addData) {
        try {
            $thisModel = new self();
            $thisModel->id = isset($addData['id']) ? trim($addData['id']) : null;//
            $thisModel->status = self::NO_STATUS;//是否有效[1有效，2无效，0屏蔽
            $thisModel->create_time = date('Y-m-d H:i:s');
            $thisModel->name = isset($addData['name']) ? trim($addData['name']) : null;
            $thisModel->syn_id = isset($addData['syn_id']) ? trim($addData['syn_id']) : null;
            $thisModel->is_delete = isset($addData['is_delete']) ? trim($addData['is_delete']) : null;
            $thisModel->enable = isset($addData['enable']) ? trim($addData['enable']) : null;
            $thisModel->code = isset($addData['code']) ? trim($addData['code']) : "";//模型代码
            $thisModel->order_id = isset($addData['order_id']) ? trim($addData['order_id']) : null;
            $thisModel->state_code = isset($addData['state_code']) ? trim($addData['state_code']) : null;
            $thisModel->status = isset($addData['status']) ? trim($addData['status']) : null;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'data' => [
                    'addData' => $addData
                ],
                'exception' => $e,
            ];
            DmpLog::error('insert_addr_province_error', $log);
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
                $datainfo->update_time = date('Y-m-d H:i:s');
                return $datainfo->save();
            }
            return false;
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'data' => $updateInfo,
            ];
            DmpLog::error('update_addr_province_error', $log);
            return false;
        }
    }
    /**
     * 获取省份数据集
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @return array|\yii\db\ActiveRecord[]
     */
	public static function getDatas($params = [], $orderBy = [], $offset = 0, $limit = 100, $fied=['*']) {
        $query = self::find()->select($fied);
        if(!empty($params)) {
            foreach($params as $k=>$v) {
                if(is_array($v)) {
                    $query -> andWhere($v);
                } else {
                    $query -> andWhere([$k=>$v]);
                }
            }
        }
        if ($limit !== -1) {
            $query -> offset($offset);
            $query -> limit($limit);
        }
        if (!empty($orderBy)) {
            $query -> orderBy($orderBy);
        }
        $projectList = $query->asArray()->all();
//        var_dump($query->createCommand()->getRawSql());die;
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
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListData($params = [], $orderBy = [], $offset = 0, $limit = 10, $fied=['*']) {
        try {
            $dataList = self::getDatas($params, $orderBy, $offset, $limit, $fied);
            $data = [
                'dataList' => $dataList,
                'count' => 0,
            ];
            if(!empty($dataList)) {
                $count = self::getCount($params);
                $data['count'] = $count;
            }
            return $data;
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'params' => [
                    'where' => $params,
                    'orderBy' => $orderBy,
                    'offset' => $offset,
                    'limit' => $limit,
                    'fied' => $fied,
                ],
            ];
            DmpLog::warning('getListData_addr_province_error', $log);
            return [];
        }
    }
    /**
     * 获取总数量
     * @param $params
     * @return int
     */
    public static function getCount($params, $fied=['*']) {
        try {
            $query = self::find()->select($fied);
            if(!empty($params)) {
                foreach($params as $k=>$v) {
                    if(is_array($v)) {
                        $query -> andWhere($v);
                    } else {
                        $query -> andWhere([$k=>$v]);
                    }
                }
            }
//            var_dump($query->createCommand()->getRawSql());die;
//                return $query->createCommand()->getRawSql();
            return  $query->count();
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'params' => $params,
            ];
            DmpLog::warning('getCount_addr_province_error', $log);
            return 0;
        }
    }
}
