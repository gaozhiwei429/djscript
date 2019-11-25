<?php
/**
 * crontab commands
 * @文件名称: AnniversaryController.php
 * @author: jawei
 * @Email: gaozhiwei@etcp.cn
 * @Date: 2017-06-06
 * @Copyright: 2017 悦畅科技有限公司. All rights reserved.
 * 注意：本内容仅限于悦畅科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace commands;

use appcomponents\modules\passport\PassportService;
use source\libs\DmpRedis;
use source\libs\DmpUtil;
use source\manager\BaseService;
use yii\console\Controller;

class AnniversaryController extends Controller{
    //入党纪念【每60分钟即每小时执行一次*/60  * * * *，每24小时（每午夜）执行一次0 0 * * *】
    //0 0 * * * /usr/bin/php /data/www/dangjian-script/yii anniversary/join-organization>> /data/logs//data/www/dangjian-script/crontab/$(date +\%Y\%m\%d)-anniversary-join-organization.log
    /**
     * 入党纪念数据获取队列维护
     * @return array
     */
	public function actionJoinOrganization() {
        $dmpUtil = new DmpUtil();
        $startTime = time();
        $startDateTime = date("Y-m-d",strtotime("-1 day"))." 0:0:0";
        $endDateTime = date("Y-m-d",strtotime("-1 day"))." 23:59:59";
        $redisKey = \Yii::$app->params['redis_key']['anniversary']['join-organization'];//入党纪念
//        $passportParams[] = ['>=', 'create_time', $startDateTime];
        $passportParams[] = ['<=', 'create_time', $endDateTime];
        $passportService = new PassportService();
        $p = 1;
        $size = 100;
        $ret = $passportService->getList($passportParams, ['id'=>SORT_ASC], $p, $size, ['id','create_time'], false, false);
        if(BaseService::checkRetIsOk($ret)) {
            $count = 0;
            $passportDataList = BaseService::getRetData($ret);
            if(isset($passportDataList['count']) && !empty($passportDataList['count'])) {
                $count = $passportDataList['count'];
            }
            $countPage = intval(ceil($count/$size));
            $dmpRedis = new DmpRedis();
            for($i=0; $i<=$countPage; $i++) {
                if($i>$p) {
                    $ret = $passportService->getList($passportParams, ['id'=>SORT_ASC], $i, $size, ['id','create_time'], false, false);
                    $passportDataList = BaseService::getRetData($ret);
                }
                //循环数据入队列
                if(isset($passportDataList['dataList']) && !empty($passportDataList['dataList'])) {
                    foreach($passportDataList['dataList'] as $dataInfo) {
                        $flag = date('m-d', strtotime($dataInfo['create_time']));
                        $passportUserInfoRet = $passportService->getUserInfoByUserId($dataInfo['id']);
                        if(BaseService::checkRetIsOk($passportUserInfoRet)) {
                            $passportUserInfo = BaseService::getRetData($passportUserInfoRet);
                            $avatar_img = isset($passportUserInfo['avatar_img']) ? $passportUserInfo['avatar_img'] : "";
                            $full_name = isset($passportUserInfo['full_name']) ? $passportUserInfo['full_name'] : "";
                            if($avatar_img && $full_name) {
                                $data = [
                                    'user_id' => $dataInfo['id'],
                                    'avatar_img' => isset($passportUserInfo['avatar_img']) ? $passportUserInfo['avatar_img'] : "",
                                    'sex' => isset($passportUserInfo['sex']) ? $passportUserInfo['sex'] : "",
                                    'full_name' => isset($passportUserInfo['full_name']) ? $passportUserInfo['full_name'] : "",
                                    'nickname' => isset($dataInfo['nickname']) ? $dataInfo['nickname'] : "",
                                    'user_status' => isset($passportUserInfo['user_status']) ? $passportUserInfo['user_status'] : 0,
                                ];
                                $dmpRedis->hmset($redisKey.":".$flag, $dataInfo['id'], $data);
                            }
                        }
                    }
                }
            }
            $data = $dmpRedis->get_redis_page_info("anniversary_join_organization:01-11", 1,13,array('user_id','avatar_img'));
            var_dump($data);die;
        }
        $endTime = time();
        $dmpUtil->dump($ret);
        $dmpUtil->dump('executtime:'.($endTime-$startTime)."s"."   startTime:".date('Y-m-d H:i:s', $startTime)."   endTime:".date('Y-m-d H:i:s', $endTime));
    }

    //入党纪念【每60分钟即每小时执行一次*/60  * * * *，每24小时（每午夜）执行一次0 0 * * *】
    //0 0 * * * /usr/bin/php /data/www/dangjian-script/yii anniversary/join>> /data/logs//data/www/dangjian-script/crontab/$(date +\%Y\%m\%d)-anniversary-join.log
    /**
     * 入党纪念数据获取队列维护
     * @return array
     */
    public function actionJoin() {
        $dmpUtil = new DmpUtil();
        $startTime = time();
        $startDateTime = date("Y-m-d",strtotime("-1 day"))." 0:0:0";
        $endDateTime = date("Y-m-d",strtotime("-1 day"))." 23:59:59";
        $redisKey = \Yii::$app->params['redis_key']['anniversary']['join-organization'];//入党纪念
//        $passportParams[] = ['>=', 'create_time', $startDateTime];
        $passportParams[] = ['=', 'create_time_year', ""];
        $passportParams[] = ['=', 'create_time_month', ""];
        $passportParams[] = ['=', 'create_time_day', ""];
        $passportParams[] = ['!=', 'avatar_img', ""];
        $passportParams[] = ['!=', 'full_name', ""];
        $passportService = new PassportService();
        $p = 1;
        $size = 100;
        $ret = $passportService->getUserInfoList($passportParams, ['id'=>SORT_ASC], $p, $size, ['user_id']);
        if(BaseService::checkRetIsOk($ret)) {
            $count = 0;
            $passportDataList = BaseService::getRetData($ret);
            if(isset($passportDataList['count']) && !empty($passportDataList['count'])) {
                $count = $passportDataList['count'];
            }
            $countPage = intval(ceil($count/$size));
            $dmpRedis = new DmpRedis();
            for($i=0; $i<=$countPage; $i++) {
                if($i>$p) {
                    $ret = $passportService->getUserInfoList($passportParams, ['id'=>SORT_ASC], $i, $size, ['user_id']);
                    $passportDataList = BaseService::getRetData($ret);
                }
                //循环数据入队列
                if(isset($passportDataList['dataList']) && !empty($passportDataList['dataList'])) {
                    foreach($passportDataList['dataList'] as $dataInfo) {
                        $passportUserInfoRet = $passportService->getUserInfoByUserId($dataInfo['user_id']);
                        if(BaseService::checkRetIsOk($passportUserInfoRet)) {
                            $passportUserInfo = BaseService::getRetData($passportUserInfoRet);
                            $create_time = isset($passportUserInfo['create_time']) ? $passportUserInfo['create_time'] : "";
                            $year = date('Y', strtotime($create_time));
                            $month = date('m', strtotime($create_time));
                            $day = date('d', strtotime($create_time));
                            $updateData = [
                                'create_time_year' => $year,
                                'create_time_month' => $month,
                                'create_time_day' => $day,
                            ];
                            $updateParams = [];
                            $updateParams['user_id'] = $dataInfo['user_id'];//['=', , ];
                            $ret = $passportService->updateUserInfoModelByParams($updateParams, $updateData);
                        }
                    }
                }
            }
        }
        $endTime = time();
        $dmpUtil->dump($ret);
        $dmpUtil->dump('executtime:'.($endTime-$startTime)."s"."   startTime:".date('Y-m-d H:i:s', $startTime)."   endTime:".date('Y-m-d H:i:s', $endTime));
    }
}
