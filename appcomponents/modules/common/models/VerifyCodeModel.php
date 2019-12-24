<?php
/**
 * user相关的model实现
 * @文件名称: UserModel.php
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

class VerifyCodeModel extends BaseModel
{
    //验证类型【1邮箱，2手机号，3QQ，4新浪】
    const EMAIL_TYPE = 1;
    const MOBILE_TYPE = 2;
    const QQ_TYPE = 3;
    const SINA_TYPE = 4;

    //是否已验证【0,已验证无效，1未验证】
    const IS_VALID = 1;
    const NO_VALID = 0;
    //是否被删除
    const IS_DEL = 1;
    const NO_DEL = 0;

    public static function tableName() {
        return '{{%verify_code}}';
    }
    public function getSource() {
        return [
            self::EMAIL_TYPE => '邮箱',
            self::MOBILE_TYPE => '手机号',
            self::QQ_TYPE => 'QQ',
            self::SINA_TYPE => '新浪',
        ];
    }
    /**
     * 获取验证类型
     * @return array
     */
    public function getTypes() {
        return [
            self::EMAIL_TYPE,
            self::MOBILE_TYPE,
            self::QQ_TYPE,
            self::SINA_TYPE,
        ];
    }
    /**
     * 根据验证值获取最后一条信息
     * @param $verify_value
     * @param int $type
     * @return mixed
     */
    public function getInfoByValue($params, $type=self::MOBILE_TYPE){
        if(!$type) {
            $type = self::MOBILE_TYPE;
        }
        $params['type'] = $type;
        return $this->getOne($params);
    }
    /**
     * 添加验证码记录表
     * @param $addData
     * @return bool|string
     */
    public function addInfo($addData) {
        try {
            $mobile_overdue_time = isset(Yii::$app->params['verify']['moblie']['mobile_overdue_time']) ?
                Yii::$app->params['verify']['moblie']['mobile_overdue_time'] : 60*15;
            $thisModel = new self();
            $thisModel->code = isset($addData['code']) ? trim($addData['code']) : null;//验证码
            $thisModel->type = isset($addData['type']) ? (int)$addData['type'] : self::MOBILE_TYPE;//验证类型【1邮箱，2手机号，3QQ，4新浪】
            $thisModel->verify_value = isset($addData['verify_value']) ? trim($addData['verify_value']) : '';//验证账号
            $thisModel->create_time = date('Y-m-d H:i:s');
            $thisModel->isvalid = isset($addData['isvalid']) ? (int)$addData['isvalid'] : self::IS_VALID;//是否已验证【0,已验证无效，1未验证】
            $thisModel->overdue_time = date('Y-m-d H:i:s', time()+$mobile_overdue_time);//短信验证码过期时间
            $thisModel->max_verify_times = isset($addData['max_verify_times']) ? (int)$addData['max_verify_times'] : 1;//每个验证码最大验证次数
            $thisModel->client_ip = isset($addData['client_ip']) ? trim($addData['client_ip']) : Yii::$app->request->userIP;//发送短信客户端ip
            $thisModel->is_del = isset($addData['is_del']) ? trim($addData['is_del']) : 0;//是否被删除【1已经删除，0未删除】
            $thisModel->save();
//            var_dump($this::find()->createCommand()->getRawSql());die;
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_verifyCode_error', $e);
            return false;
        }
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
        } catch (BaseException $e) {
            DmpLog::warning('getListData_VerifyCodeModel_error', $e);
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
        } catch (BaseException $e) {
            DmpLog::warning('getCount_VerifyCodeModel_error', $e);
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
            $thisModel->inserttime = time();
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
            DmpLog::error('insert_verifyCodeModel_error', $e);
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
                $datainfo->updatetime = time();
                return $datainfo->save();
            }
            return false;
        } catch (BaseException $e) {
            DmpLog::warning('update_verifyCodeModel_exception', $e);
            return false;
        }
    }
}
