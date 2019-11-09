<?php
/**
 * 运营平台组织相关的管理表
 * @文件名称: OrganizationModel
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

class OrganizationModel extends BaseModel
{
    const ON_LINE_STATUS = 1;//已上线
    const BEFORT_STATUS = 0;//已下线
    public static function tableName() {
        return '{{%organization}}';
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
    public static function getListData($params = [], $orderBy = [], $offset = 0, $limit = 10, $fied=['*'], $index=false) {
        try {
            $dataList = self::getDatas($params, $orderBy, $offset, $limit, $fied);
            if($index) {
                foreach($dataList as $k=>$v) {
                    $dataList[$v['uuid']] = $v;
                    unset($dataList[$k]);
                }
            }
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
            $thisModel->title = isset($addData['title']) ? trim($addData['title']) : "";//名称
            $thisModel->sort = isset($addData['sort']) ? intval($addData['sort']) : 0;
            $thisModel->content = isset($addData['content']) ? trim($addData['content']) : "";
            $thisModel->parent_uuid = isset($addData['parent_uuid']) ? trim($addData['parent_uuid']) : "";
            $thisModel->establish_time = isset($addData['establish_time']) ? trim($addData['establish_time']) : date('Y-m-d H:i:s');
            $thisModel->status = isset($addData['status']) ? intval($addData['status']) : self::ON_LINE_STATUS;
            $thisModel->organization_type = isset($addData['organization_type']) ? trim($addData['organization_type']) : ""; //组织类型
            $thisModel->branch_type = isset($addData['branch_type']) ? trim($addData['branch_type']) : ""; //支部类型
            $thisModel->address = isset($addData['address']) ? trim($addData['address']) : ""; //联系地址
            $thisModel->contacts = isset($addData['contacts']) ? trim($addData['contacts']) : ""; //联系人电话
            $thisModel->longitude_and_latitude = isset($addData['longitude_and_latitude']) ? trim($addData['longitude_and_latitude']) : ""; //经度和纬度
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
