<?php

/**
 * 入口
 * @文件名称: web.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */

namespace backend\controllers;
use yii\base\Controller;
use Yii;

class SiteController extends Controller {
    public function beforeAction($action) {
        $url = Yii::$app->request->hostInfo."/index.html";
        header("Location: $url");
    }
    public function actionIndex() {
        $url = Yii::$app->request->hostInfo."/index.html";
        header("Location: $url");
    }
}