<?php
/**
 * 活动管理表
 * @文件名称: ActivityModel
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

class ActivityModel extends BaseModel
{
    const WAIT_APPROVAL_STATUS = 1;//待审批
    const ALREADY_APPROVAL_STATUS = 2;//已审批
    const BEFORT_STATUS = 0;//禁用
    public static function tableName() {
        return '{{%activity}}';
    }
    /**
     * 根据条件获取最后一条信息
     * @param $verify_value
     * @param int $type
     * @return mixed
     */
    public function getInfoByValue($params,$field=['*']){
        return $this->getOne($params,$field);
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
     * 获取数据展示
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
            $thisModel->title = isset($addData['title']) ? trim($addData['title']) : "";//名称
            $thisModel->content = isset($addData['content']) ? trim($addData['content']) : "";//名称
            $thisModel->address = isset($addData['address']) ? trim($addData['address']) : "";//会议地址
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::ALREADY_APPROVAL_STATUS;
            $thisModel->join_people_num = isset($addData['join_people_num']) ? intval($addData['join_people_num']) : 0;//参加人数
            $thisModel->waiver_people_num = isset($addData['waiver_people_num']) ? intval($addData['waiver_people_num']) : 0;//弃权人数
            $thisModel->pending_people_num = isset($addData['pending_people_num']) ? intval($addData['pending_people_num']) : 0;//待定人数
            $thisModel->sort = isset($addData['sort']) ? intval($addData['sort']) : 0;
            $thisModel->start_time = isset($addData['start_time']) ? trim($addData['start_time']) : "";
            $thisModel->end_time = isset($addData['end_time']) ? trim($addData['end_time']) : "";
            $thisModel->organization_id = isset($addData['organization_id']) ? intval($addData['organization_id']) : 0;
            $thisModel->save();
            return Yii::$app->db->getLastInsertID();
//            return $isSave;
        } catch (BaseException $e) {
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
            return false;
        }
    }
}
