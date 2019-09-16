<?php
/**
 * 主订单相关的接口定时任务
 * crontab commands
 * @文件名称: OrderController.php
 * @author: jawei
 * @Email: gaozhiwei@etcp.cn
 * @Date: 2017-06-06
 * @Copyright: 2017 悦畅科技有限公司. All rights reserved.
 * 注意：本内容仅限于悦畅科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace commands;

use source\libs\DmpRedis;
use source\libs\DmpUtil;
use source\manager\BaseService;
use yii\console\Controller;
use Yii;

class PayController extends Controller{
    //支付成功后未更新成功支付单号的话通过任务处理
    //*/5 * * * * /bin/php /data/www/wbaole/yii pay/already-pay-not-update-order-status>> /data/logs/crontab/$(date +\%Y\%m\%d)-already-pay-not-update-order-status.log
    /**
     * 支付成功后未更新成功支付单号的话通过任务处理
     */
	public function actionAlreadyPayNotUpdateOrderStatus() {
        $dmpUtil = new DmpUtil();
        $startData = date('2017-01-01');
        $endData = date('2019-12-26');
        $startTime = time();
        $key = isset(Yii::$app->params['rediskey']['pay']['notUpdateOrderStatus']) ? Yii::$app->params['rediskey']['pay']['notUpdateOrderStatus'] : '';
        if(empty($key)) {
            $ret = BaseService::returnErrData(0, 53300, "支付成功后未更新成功redis配置key不存在");
        } else {

        }
        $endTime = time();
        $dmpUtil->dump($ret);
        $dmpUtil->dump('executtime:'.($endTime-$startTime)."s"."   startTime:".date('Y-m-d H:i:s', $startTime)."   endTime:".date('Y-m-d H:i:s', $endTime));
	}
    //订单有效期为半个小时所以每1分钟执行一次脚本执行将过期未支付的主订单队列状态更新为已取消
    //*/1 * * * * /bin/php /data/www/TXHWebServer/yii order/update-overdue-order-status>> /data/logs/crontab/$(date +\%Y\%m\%d)-update-overdue-order-status.log
    /**
     * 更新过期的订单的状态为已取消订单
     */
    public function actionUpdateOverdueOrderStatus() {
        $key = isset(Yii::$app->params['rediskey']['order']['overdueorder']) ? Yii::$app->params['rediskey']['order']['overdueorder'] : '';
        $dmpUtil = new DmpUtil();
        $startTime = time();
        $cancelContent = isset(Yii::$app->params['order']['cancelContent']['longTimeNoPay']) ? Yii::$app->params['order']['cancelContent']['longTimeNoPay'] : '';
        $crontabService = new CrontabService();
        $ret = $crontabService->UpdateOverdueOrderStatus($key, $cancelContent);
        $dmpUtil->dump($ret);
        $endTime = time();
        $dmpUtil->dump('executtime:'.($endTime-$startTime)."s"."   startTime:".date('Y-m-d H:i:s', $startTime)."   endTime:".date('Y-m-d H:i:s', $endTime));
    }

    /**
     * 商家下线会自动取消订单
     */
    public function actionUpdateBusinessOfflineOrderStatus($key, $cancelContent) {

    }
    /**
     * 商家服务下线
     */
    public function actionUpdateBusinessServiceOfflineOrderStatus($key, $cancelContent) {

    }

    /**
     * 核销码已经过期需要自动修改已经支付未使用的订单状态为已失效状态【用户可以去申请退款】
     */
    public function actionUpdateOrderStatusToVerification() {

    }
}
