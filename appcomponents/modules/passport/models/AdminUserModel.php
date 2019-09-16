<?php
/**
 * 后台登陆用户账户存储表
 * @文件名称: AdminUserModel.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-06-06
 */
namespace appcomponents\modules\passport\models;
use source\libs\DmpLog;
use source\manager\BaseException;
use source\models\BaseModel;
use Yii;
class AdminUserModel extends BaseModel {
    public static function tableName() {
        return '{{%admin_user}}';
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
            $thisModel->salt = isset($addData['salt']) ? trim($addData['salt']) : null;
            $thisModel->password = isset($addData['password']) ? trim($addData['password']) : null;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_user_error', $e);
            return false;
        }
    }
}
