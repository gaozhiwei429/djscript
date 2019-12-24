<?php
/**
 * 运营平台用户与党组织关系管理表
 * @文件名称: UserOrganizationModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-06-06
 * @Copyright: 2017 北京往全保有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;

class UserOrganizationModel extends BaseModel
{
    const ON_LINE_STATUS = 1;//已上线
    const DELECT_STATUS = 0;//删除
    public static function tableName() {
        return '{{%user_organization}}';
    }
    /**
     * 根据条件获取最后一条信息
     * @param $verify_value
     * @param int $type
     * @return mixed
     */
    public function getInfoByValue($params){
        return $this->getOne($params);
    }
    /**
     * 获取数据集
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDatas($params = [], $orderBy = [], $offset = 0, $limit = 100, $fied=['*'], $groupBy=[]) {
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
        if (!empty($groupBy) && is_array($groupBy)) {
            $query->groupBy($groupBy);
        }
        if ($limit !== -1) {
            $query -> offset($offset);
            $query -> limit($limit);
        }
        if (!empty($orderBy)) {
            $query -> orderBy($orderBy);
        }
        $projectList = $query->asArray()->all();
        return $projectList;
    }
    /**
     * 获取banner首页数据展示
     * @param array $params
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @param array $fied
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDataArr($params = [], $orderBy = [], $offset = 0, $limit = 10, $fied=['*']) {
        return $dataList = self::getDatas($params, $orderBy, $offset, $limit, $fied);
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
    public static function getListData($params = [], $orderBy = [], $offset = 0, $limit = 10, $fied=['*'], $groupBy=[]) {
        try {
            $dataList = self::getDatas($params, $orderBy, $offset, $limit, $fied, $groupBy);
            $data = [
                'dataList' => $dataList,
                'count' => 0,
            ];
            if(!empty($dataList)) {
                $count = self::getCount($params, $groupBy);
                $data['count'] = $count;
            }
            return $data;
//            $query->createCommand()->getRawSql();
        } catch (BaseException $e) {
            DmpLog::warning('getListData_user_organization_error', $e);
            return [];
        }
    }
    /**
     * 获取总数量
     * @param $params
     * @return int
     */
    public static function getCount($params, $groupBy=[]) {
        try {
            $query = self::find()->select(['id']);
            if(!empty($params)) {
                foreach($params as $k=>$v) {
                    if(is_array($v)) {
                        $query -> andWhere($v);
                    } else {
                        $query -> andWhere([$k=>$v]);
                    }
                }
            }
            if (!empty($groupBy) && is_array($groupBy)) {
                $query->groupBy($groupBy);
            }
//                return $query->createCommand()->getRawSql();
            return  $query->count();
        } catch (BaseException $e) {
            DmpLog::warning('getCount_user_organization_error', $e);
            return 0;
        }
    }

    /**
     * 添加一条记录表
     * @param $addData
     * @return bool|string
     */
    public function addInfo($addData) {
        try {
            $thisModel = new self();
            $thisModel->id = isset($addData['id']) ? trim($addData['id']) : null;
            $thisModel->user_id = isset($addData['user_id']) ? intval($addData['user_id']) : 0;
            $thisModel->organization_id = isset($addData['organization_id']) ? intval($addData['organization_id']) : 0;
            $thisModel->level_id = isset($addData['level_id']) ? intval($addData['level_id']) : 0;
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::ON_LINE_STATUS;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_user_organization_error', $e);
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
            DmpLog::error('update_user_organization_error', $e);
            return false;
        }
    }
    /**
     * 批量更新循环周期
     * @param array $condition
     * $condition = ['advertise_id' => '','status' => '', 'weekdays'=>[1,2,3]] 查询条件
     * $params = ['status' => '']
     * @param $params
     * @return bool
     */
    public function batchUpdate($condition = [], $params)
    {
        if (count($condition) == 0 || !is_array($condition) || count($params) == 0) {
            return false;
        }
        $conditions = ' 1 = 1 ';
        $bind = [];
        foreach($condition as $k=>$v) {
            $conditions .= " AND `$k` = :$k";
            $bind["$k"] = $v;
        }
        $result = self::updateAll($params, $conditions, $bind);

        return $result > 0 ? true : false;
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
}
