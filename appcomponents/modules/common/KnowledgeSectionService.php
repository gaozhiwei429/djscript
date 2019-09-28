<?php
/**
 * 知识库学习内容章节数据service
 * @文件名称: KnowledgeSectionService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common;
use appcomponents\modules\common\models\KnowledgeSectionModel;
use source\libs\Common;
use source\manager\BaseService;
use Yii;
class KnowledgeSectionService extends BaseService
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
     * C端知识库章节数据列表获取
     * @param $addData
     * @return array
     */
    public function getList($params = [], $orderBy = [], $p = 1, $limit = 10, $fied=['*']) {
        $Common = new Common();
        $offset = $Common->getOffset($limit, $p);
        $knowledgeSectionModel = new KnowledgeSectionModel();
        $params[] = ['=', 'status', $knowledgeSectionModel::ON_LINE_STATUS];
        $knowledgeSectionList = $knowledgeSectionModel->getListData($params, $orderBy, $offset, $limit, $fied);
        if(!empty($knowledgeSectionList)) {
            return BaseService::returnOkData($knowledgeSectionList);
        }
        return BaseService::returnErrData([], 500, "暂无数据");
    }
}
