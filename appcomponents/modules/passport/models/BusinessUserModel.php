<?php
/**
 * 用户账号存储表
 * @文件名称: BusinessUserModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-06-06
 */
namespace appcomponents\modules\passport\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;
class BusinessUserModel extends BaseModel {
    public static function tableName() {
        return '{{%business_user}}';
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
            $thisModel->username = isset($addData['username']) ? trim($addData['username']) : null;
            $thisModel->password = isset($addData['password']) ? trim($addData['password']) : null;
            $thisModel->salt = isset($addData['salt']) ? trim($addData['salt']) : null;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
//            $log = [
//                'msg'    => $e->getMessage(),
//                'data' => [
//                    'addData' => $addData
//                ],
//                'exception' => $e,
//            ];
            DmpLog::error('insert_user_error', $e);
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
            if(!empty($updateInfo) && !empty($datainfo)) {
                foreach($updateInfo as $k=>$v) {
                    $datainfo->$k = trim($v);
                }
                return $datainfo->save();
            }
            return false;
        } catch (BaseException $e) {
            DmpLog::error('update_user_error', $e);
            return false;
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
            DmpLog::warning('getCount_user_error', $e);
            return 0;
        }
    }

}
