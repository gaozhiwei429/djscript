<?php
/**
 * 产品的评论反馈信息数据service
 * @文件名称: FeedbackService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\FeedbackModel;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class FeedbackService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\common\controllers';
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * C端Feeback数据获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $feedbackModel = new FeedbackModel();
        $params[] = ['=', 'status', $feedbackModel::ONLINE_STATUS];
        $cityList = $feedbackModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($cityList)) {
            return BaseService::returnOkData($cityList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
    /**
     * 评论信息记录入库
     * @param $user_id 提交者用户id
     * @param $content 提交内容
     * @param int $type_id 提交对象的所属分类
     * @param int $object_id 提交对象的唯一标识
     * @param int $score 提交对象的打分数值
     * @return array
     */
    public function addFeedBackData($user_id, $content, $object_id=0, $type_id=10, $score=50) {
        if(empty($object_id)) {
            return BaseService::returnErrData([], 55700, "请求参数异常");
        }
        $feedbackModel = new FeedbackModel();
        $data['user_id'] = $user_id;
        $data['content'] = $content;
        $data['type_id'] = $type_id;
        $data['project_id'] = $object_id;
        $data['score'] = $score;
        $feedback = $feedbackModel->addData($data);
        if($feedback) {
            return BaseService::returnOkData($feedback);
        }
        return BaseService::returnErrData([], 56700, "信息记录失败");
    }
}
