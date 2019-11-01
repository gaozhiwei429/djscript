<?php
/**
 * 资讯相关的分类操作
 * @文件名称: TypeController.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Date: 2017-12-06
 * @Copyright: 2017 北京往全包科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全包科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\common\controllers;
use appcomponents\modules\common\TypeService;
use source\controllers\BaseController;
use \Yii;
class TypeController extends BaseController
{
    /**
     * 用户登录态基础类验证
     * @return array
     */
    public function beforeAction($action){
        return parent::beforeAction($action);
    }
    /**
     * 资讯分类
     * @return string
     */
    public function actionIndex() {
        $typeService = new TypeService();
        $params[] = ['=', 'status', 1];
        return $typeService->getTree($params, ['sort'=>SORT_DESC,'id'=>SORT_DESC], 1, -1, ['id','title','use_id','parent_id'], true);
    }
}
