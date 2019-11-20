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

    /**
     * 获取树状管理结构数据
     * @return array
     */
    public function actionGetTrees() {
        $page = intval(Yii::$app->request->post('p', 1));
        $size = intval(Yii::$app->request->post('size', -1));
        $parent_id = intval(Yii::$app->request->post('parent_id', 0));
        $typeService = new TypeService();
        $params = [];
        $params[] = ['=', 'status', 1];
        if(!empty($parent_id)) {
            $params[] = ['=', 'parent_id', $parent_id];
        }
        return $typeService->getTree($params, ['id'=>SORT_DESC, 'sort'=>SORT_DESC], $page, $size,  ['id','title','use_id','parent_id'], true);
    }
}
