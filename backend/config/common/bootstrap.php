<?php

/**
 * bootstrap配置
 * @文件名称: bootstrap.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
/*Yii::setAlias('@common', dirname(__DIR__));*/
Yii::setAlias('source', dirname(dirname(dirname(__DIR__))). '/source');
Yii::setAlias('@backend', dirname(dirname(dirname(__DIR__))) . '/backend');
Yii::setAlias('@infrastructure',dirname(dirname(dirname(__DIR__))) . '/infrastructure');
Yii::setAlias('@appcomponents', dirname(dirname(dirname(__DIR__))) . '/appcomponents');
Yii::setAlias('@commands', dirname(dirname(dirname(__DIR__))) . '/commands');
//Yii::setAlias('@alipay', '@vendor/alipay');
