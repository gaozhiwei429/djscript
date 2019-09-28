<?php
/**
 * 知识库相关的数据获取service
 * @文件名称: KnowledgeService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\KnowledgeModel;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class KnowledgeService extends BaseService
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
     * C端知识库数据列表
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $knowledgeModel = new KnowledgeModel();
        $params[] = ['=', 'status', $knowledgeModel::ON_LINE_STATUS];
        $knowledgeList = $knowledgeModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($knowledgeList)) {
            return BaseService::returnOkData($knowledgeList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
}
