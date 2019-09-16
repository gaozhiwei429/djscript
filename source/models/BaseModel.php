<?php
/**
 * 所有model层应有的base继承
 * @文件名称: BaseModel.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord {
    static $startTime;
    public $endTime;
    static $query;
    public function __construct() {
        $this::$query = self::find();
        $this::$startTime =time();
    }
    /**
     * 指定数据库db操作
     * @return \yii\db\Connection
     */
    public static function getDb(){
        return Yii::$app->db;
    }
    public function __destruct() {
        if(isset(Yii::$app->params['print_sql_log']) && Yii::$app->params['print_sql_log']) {
            try {
                DmpLog::sqlLog($this::$query->createCommand()->getRawSql());
            } catch(Exception $e) {
                DmpLog::error('base_model_error', $e);
            }
        }
    }
    /**
     * 根据条件获取数据基本信息
     * @params  array   $where      索引条件
     * @params  array   $field      获取的指定字段,
     */
    public function getOne($where, $field=['*'], $orderBy = ['id' => SORT_DESC], $is_obj=0) {
        if(empty($orderBy)) {
            $orderBy = ['id' => SORT_DESC];
        }
        $query = self::find()->select($field);
        if(!empty($where)) {
            foreach($where as $k=>$v) {
                if(is_array($v)) {
                    $query->andWhere($v);
                } else {
                    $query->andWhere([$k=>$v]);
                }
            }
        }
        try {
            if(!empty($orderBy)) {
                $query->orderBy($orderBy);
            }
            if($is_obj) {
                return $query ->one();
            }
            $rest = $query ->asArray()->one();
//            DmpLog::debug($query->createCommand()->getRawSql());
//            return $query->createCommand()->getRawSql();
            return $rest;
        } catch (BaseException $e) {
            DmpLog::error('base_model_getOne_error', $e);
            return [];
        }
    }
    /**
     * 获取数据列表
     *
     * @params  array   $where      索引条件
     * @params  array   $field      获取的指定字段,
     * @params  int     $lenth      数据条数
     * @params  int     $offset     开始位置
     * @params  array   $field      获取的字段,*代表全部
     * @params  array   $orderBy    排序的规则
     */
    public function getList($where,
                            $lenth = 10, $offset = 0, $field=['*'], $orderBy = [], $groupBy = []) {
        $query = self::find()->select($field);
        if(!empty($where)) {
            foreach($where as $k=>$v) {
                if(is_array($v)) {
                    $query -> andWhere($v);
                } else {
                    $query -> andWhere([$k=>$v]);
                }
            }
        }
        if ($lenth !== -1) {
            $query -> offset($offset);
            $query -> limit($lenth);
        }
        if (!empty($orderBy)) {
            $query -> orderBy($orderBy);
        }

        if (!empty($groupBy) && is_array($groupBy)) {
            $query->groupBy($groupBy);
        }
        try {
            $rest = $query ->asArray()->all();
//        var_dump($query->createCommand()->getRawSql());die;
            return $rest;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_getList_exception', $e);
            return [];
        }
    }

    /**
     * 添加
     * @return int
     * @author Me
     **/
    public function addRow($data) {
        try {
            $conn = Yii::$app->db;
            $conn->open();
            $conn->createCommand()->insert($this->tableName(), $data)->execute();
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_addRow_exception', $e);
            return false;
        }
    }
    /**
     * 获取可以group 的 列表
     *
     * @return array()
     * @author Me
     **/
    public function getListGroup($where,
                                 $lenth = 10, $offset = 0, $field=['*'], $groupBy = []) {
        $query = self::find()->select($field);

        if(!empty($where)) {
            foreach($where as $k=>$v) {
                if(is_array($v)) {
                    $query -> andWhere($v);
                } else {
                    $query -> andWhere([$k=>$v]);
                }
            }
        }
        if ($lenth !== -1) {
            $query -> offset($offset);
            $query -> limit($lenth);
        }
        if (!empty($groupBy)) {
            $query -> groupBy($groupBy);
        }

        try {
            $rest = $query->asArray()->all();
            return $rest;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_getListGroup_exception', $e);
            return [];
        }
    }
    /**
     * 根据主键值查询结果数组
     * @param   $id     当前数据的主键值
     * @return    array|null|ActiveRecord
     */
    public function findIdentity($id) {
        $model = self::find();
        try {
            if($id) {
                return $model->where(['id' => $id])->asArray()->one();
            }
            return [];
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_findIdentity_exception', $e);
            return [];
        }
    }
    /**
     * 返回当前数据满足条件的总行数
     * @param   array   $where  需要查询的数据条件
     * @return  int|string
     */
    public function getTotalLine($where, $field=['id']) {
        $query = self::find()->select($field);
        try {
            if(!empty($where)) {
                foreach($where as $k=>$v) {
                    if(is_array($v)) {
                        $query -> andWhere($v);
                    } else {
                        $query -> andWhere([$k=>$v]);
                    }
                }
            }
            DmpLog::info('BaseModel_getListGroup_exception', [
                'sql'  => $query->createCommand()->getRawSql(),
                'time'  => time()-$this::$startTime,
            ]);
            return  $query->count();
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_getListGroup_error', $e);
            return 0;
        }
    }
    /**
     * 根据主键id数组获取数据列表
     * @param array $idArr
     * @param int $lenth
     * @param int $offset
     * @param array $field
     * @param array $orderBy
     * @return array
     */
    public function getListByIdArr($idArr, $lenth = 10, $offset = 0, $field=['*'], $orderBy = ['id' => SORT_ASC]) {
        try {
            $restArr = [];
            $query = self::find()->select($field)->orderBy($orderBy);
            if(!empty($idArr)) {
                $query -> andWhere(['in','id',$idArr]);
            }
            if($lenth!=-1) {
                $query -> offset($offset);
                $query -> limit($lenth);
            }
            $rest = $query ->asArray()->all();
            if(!empty($rest)) {
                foreach($rest as $v) {
                    $restArr[$v['id']] = $v;
                }
            }
            return $restArr;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_getListByIdArr_error', $e);
            return [];
        }
    }
    /**
     * 根据条件数组获取数据列表
     * @param array $idArr
     * @param int $lenth
     * @param int $offset
     * @param array $field
     * @param array $orderBy
     * @return array
     */
    public function getListByParams($params, $lenth = 10, $offset = 0, $field=['*'], $orderBy = ['id' => SORT_ASC]) {
        try {
            $restArr = [];
            $query = self::find()->select($field)->orderBy($orderBy);
            if(!empty($params)) {
                foreach($params as $k=>$v) {
                    if(is_array($v)) {
                        $query -> andWhere($v);
                    } else {
                        $query -> andWhere([$k=>$v]);
                    }
                }
            }
            if($lenth!=-1) {
                $query -> offset($offset);
                $query -> limit($lenth);
            }
            $rest = $query ->asArray()->all();
            if(!empty($rest)) {
                foreach($rest as $v) {
                    $restArr[$v['id']] = $v;
                }
            }
            return $restArr;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_getListByParams_error', $e);
            return [];
        }
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
     * @param array $params whereArray
     * @param array $updateInfo 需要更新的数据集合
     * @return bool
     */
    public function updateInfoByParams($params, $updateInfo) {
        try {
            $datainfo = $this->find()->where($params)->orderBy(['id'=>SORT_DESC])->one();
            if(!empty($updateInfo) && !empty($datainfo)) {
                foreach($updateInfo as $k=>$v) {
                    $datainfo->$k = trim($v);
                }
                return $datainfo->save();
            }
            return false;
        } catch (BaseException $e) {
            DmpLog::error('BaseModel_updateInfoByParams_error', $e);
            return false;
        }
    }

    /**
     * 更新数据
     * @param $columns
     * @param $updateData
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateAllDataList($columns, $where) {
        if(!empty($columns) && !empty($where)) {
            try{
                return Yii::$app->db->createCommand()->update(self::tableName(), $columns, $where)->execute();
            } catch (BaseException $e) {
                DmpLog::error('BaseModel_updateAllDataList_error', $e);
                return false;
            }
        }
        return false;
    }
}
