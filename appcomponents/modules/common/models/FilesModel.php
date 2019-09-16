<?php
/**
 * 红包项目文件服务器存储表的model实现
 * @文件名称: FilesModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-06-06
 * @Copyright: 2017 北京往全保有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\models;
use source\libs\DmpLog;
use source\models\BaseModel;
use Yii;
class FilesModel extends BaseModel {
    //是否有效[1有效，2无效，0屏蔽
    const IS_STATUS = 1;
    const NO_STATUS = 2;
    const NOT_STATUS = 0;

    //是否被删除
    const IS_DEL = 1;
    const NO_DEL = 0;

    public static function tableName() {
        return '{{%files}}';
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
    public function addInfo($user_id, $addData) {
        try {
            $thisModel = new self();
            $thisModel->user_id = $user_id;//用户id
            $thisModel->status = self::NO_STATUS;//是否有效[1有效，2无效，0屏蔽
            $thisModel->create_time = date('Y-m-d H:i:s');
            $thisModel->files = isset($addData['files']) ? trim($addData['files']) : null;//文件服务器存储位置
            $thisModel->update_user_id = isset($addData['update_user_id']) ? trim($addData['update_user_id']) : null;//更新用户id
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'data' => [
                    'user_id' => $user_id,
                    'addData' => $addData
                ],
                'exception' => $e,
            ];
            DmpLog::error('insert_files_error', $log);
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
            DmpLog::error('update_files_data_error', $log);
            return false;
        }
    }
    /**
     * 批量添加文件记录数据
     * @param $user_id
     * @param $files
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addAll($user_id, $files) {
        $data = [];
        $time = date('Y-m-d H:i:s');
        $clumns = ['files', 'user_id', 'create_time'];
        foreach ($files as $k => $v) {
            $data[] = [
                trim($v),
                $user_id,
                $time
            ];
        }
        return Yii::$app->db->createCommand()->batchInsert(self::tableName(), $clumns, $data)->execute();
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
            return $query->asArray()->all();
//            $query->createCommand()->getRawSql();
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
            DmpLog::warning('getListData_filesModel_error', $log);
            return [];
        }
    }
    /**
     * 获取总数量
     * @param $params
     * @return int
     */
    public function getCount($params, $fied=['*']) {
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
        } catch (\Exception $e) {
            $log = [
                'msg'    => $e->getMessage(),
                'params' => $params,
            ];
            DmpLog::warning('getCount_filesModel_error', $log);
            return 0;
        }
    }
}
