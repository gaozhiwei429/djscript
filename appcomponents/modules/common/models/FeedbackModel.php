<?php
/**
 * 评论管理表
 * @文件名称: FeedbackModel
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

class FeedbackModel extends BaseModel
{
	//0删除，1待审核，2已审核
    const WAIT_STATUS = 1;
    const ALREADY_STATUS = 2;
    const DELECT_STATUS = 0;//删除
    public static function tableName() {
        return '{{%feedback}}';
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
        if(!empty($projectList)) {
            foreach($projectList as &$projectInfo) {
                if(isset($projectInfo['pic_url']) && !empty($projectInfo['pic_url'])) {
                    $projectInfo['pic_url'] = json_decode($projectInfo['pic_url'], true);
                }
            }
        }
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
            $thisModel->object_id = isset($addData['object_id']) ? intval($addData['object_id']) : 0;
            $thisModel->utilization_flag = isset($addData['utilization_flag']) ? intval($addData['utilization_flag']) : 0;
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::WAIT_STATUS;
            $thisModel->user_organization_id = isset($addData['user_organization_id']) ? intval($addData['user_organization_id']) : 0;//当前用户所属党组织
            $thisModel->user_organization_title = isset($addData['user_organization_title']) ? trim($addData['user_organization_title']) : "";//当前用户所属党组织名称
            $thisModel->user_level_id = isset($addData['user_level_id']) ? intval($addData['user_level_id']) : 0;//当前用户所属职务
            $thisModel->user_level_title = isset($addData['user_level_title']) ? trim($addData['user_level_title']) : "";//当前用户所属职务名称
            $thisModel->content = isset($addData['content']) ? trim($addData['content']) : "";//评论内容
            $thisModel->user_full_name = isset($addData['user_full_name']) ? trim($addData['user_full_name']) : "";//评论用户姓名冗余
            $thisModel->user_avatar_img = isset($addData['user_avatar_img']) ? trim($addData['user_avatar_img']) : "";//评论用户头像
            $thisModel->pic_url = isset($addData['pic_url']) ? trim($addData['pic_url']) : "";//评论图片
            $thisModel->is_read = isset($addData['is_read']) ? intval($addData['is_read']) : 0;//读取情况【1已读，0未读】
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
