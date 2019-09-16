<?php
/**
 * crontab commands
 * @文件名称: TestController.php
 * @author: jawei
 * @Email: gaozhiwei@etcp.cn
 * @Date: 2017-06-06
 * @Copyright: 2017 悦畅科技有限公司. All rights reserved.
 * 注意：本内容仅限于悦畅科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace commands;

use appcomponents\modules\common\BindPrinterLonAndLatService;
use source\libs\DmpUtil;
use source\manager\BaseService;
use yii\console\Controller;

class TestController extends Controller{
    //更新核销码记录的user_have_card_id字段
    //*/5 * * * * /usr/bin/php /data/www/wbaole/yii test/test>> /data/logs/crontab/$(date +\%Y\%m\%d)-test-test.log
    /**
     *
     * 更新核销码记录的user_have_card_id字段
     * @return array
     */
	public function actionTest() {
        $dmpUtil = new DmpUtil();
        $startTime = time();
        $ret = "";
        $bindPrinterLonAndLatService = new BindPrinterLonAndLatService();
        $ret = $bindPrinterLonAndLatService->addBaiduData(104);
var_dump($ret);
        $endTime = time();
        $dmpUtil->dump($ret);
        $dmpUtil->dump('executtime:'.($endTime-$startTime)."s"."   startTime:".date('Y-m-d H:i:s', $startTime)."   endTime:".date('Y-m-d H:i:s', $endTime));
    }
}
