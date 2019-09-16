<?php
/**
 * 用户基本信息存储表
 * @文件名称: UserModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-06-06
 */
namespace appcomponents\modules\passport\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;
class UserInfoModel extends BaseModel {
    public static function tableName() {
        return '{{%user_info}}';
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
            $thisModel->nickname = isset($addData['nickname']) ? trim($addData['nickname']) : "";
            $thisModel->user_id = isset($addData['user_id']) ? intval($addData['user_id']) : 0;
            $thisModel->avatar_img = isset($addData['avatar_img']) ? trim($addData['avatar_img']) : "";
            $thisModel->sex = isset($addData['sex']) ? intval($addData['sex']) : 1;
            $thisModel->birthdate = isset($addData['birthdate']) ? date('Y-m-d', strtotime(trim($addData['birthdate']))) : date('Y-m-d');
            $thisModel->email = isset($addData['email']) ? trim($addData['email']) : "";
            $thisModel->qq = isset($addData['qq']) ? trim($addData['qq']) : "";
            $thisModel->wchat = isset($addData['wchat']) ? trim($addData['wchat']) : "";
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
        } catch (BaseException $e) {
            DmpLog::error('insert_user_error', $e);
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
//            $query->createCommand()->getRawSql();
        } catch (BaseException $e) {
            DmpLog::warning('getListData_user_info_model_error', $e);
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
//                return $query->createCommand()->getRawSql();
            return  $query->count();
        } catch (BaseException $e) {
            DmpLog::warning('getCount_user_info_model_error', $e);
            return 0;
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
            DmpLog::error('insert_user_info_model_error', $e);
            return false;
        }
    }
    /**
     * 更新信息数据
     * @param int $user_id 用户ID
     * @param array $updateInfo 用户需要更新的数据集合
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
            DmpLog::warning('update_user_info_model_exception', $e);
            return false;
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
    public static function getListByTyIds($idArr, $lenth = 10, $offset = 0, $field=['*'], $orderBy = ['id' => SORT_ASC]) {
        try {
            $restArr = [];
            $query = self::find()->select($field);
            if(!empty($idArr)) {
                $query -> andWhere(['in','id',$idArr]);
            }
            if($lenth!=-1) {
                $query -> offset($offset);
                $query -> limit($lenth);
            }
            if(!empty($orderBy)) {
                $query->orderBy($orderBy);
            }
            $rest = $query ->asArray()->all();
            if(!empty($rest)) {
                foreach($rest as $v) {
                    $restArr[$v['id']] = $v;
                }
            }
            return $restArr;
        } catch (BaseException $e) {
            DmpLog::error('user_info_model_getListByTyIds_error', $e);
            return [];
        }
    }
}
