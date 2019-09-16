<?php
/**
 * 公共配置
 * @文件名称: params.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
return [
    'projectRoot' => dirname(dirname(dirname(__FILE__))),
    'logFromEmail' => true,
    'pagination' => 10, // 分页统一展示 10条记录/页
    'print_sql_log' => true,//是否需要打印sql日志
    'print_info_log' => true,//是否需要打印普通info级别日志
    'print_operation_log' => true,//是否需要打印业务日志
    'traceLevel' => [
        'info' => '3d_api_script_info',
        'warning' => '3d_api_script_warning',
        'error' => '3d_api_script_error',
        'command' => '3d_api_script_command',
        'trace' => '3d_api_script_trace',
    ],
    'upload' => [
        'qiniu' => [
            'accessKey' => 'opwxCQPEiE0lgG64ICmmzgS_opIFsgmEnYp_lVdq',
            'secretKey' => 'yUrwkmNKoRfa8XNoQ9hTmCjpuuq6aYPe8L5nE3QY',
            'bucket' => '3d-manage',
            'http' => 'http://p0fhye1yi.bkt.clouddn.com/',
        ],
        'aliyunOss' => [
            'host' => 'http://oss-cn-beijing.aliyuncs.com/',
            'dir' => 'wbaole',
            'bucket' => 'wbaole',
            'appid' => '1258502489',
            'secretId' => 'LTAIfUI66OaF2gwz',
            'secretKey' => '  bvqqtXVmv9xE3LFBTOhbtBJ6Y06kqZ',
        ],
        'tencentCos' => [
            'host' => 'https://wbaole-1258502489.cos.ap-beijing.myqcloud.com/wbaole/',
            'name' => 'wbaole',
            'appid' => '1258502489',
            'secretId' => 'AKIDHLFZUA4zTlQYcYnG9vYKm76mYGl83Auf',
            'secretKey' => ' xb1C6CVcT4B9tS2QZaDeWrCRUBWtugEn',
        ],
        'allowSize' => 8,//最大允许上传8M
        //        'allowUploadSize' => 1024 * 1024 * 8,//最大允许上传8M
        'allowUploadNum' => 8,//一次最大上传文件数
        'qrcode' => './upload/qrcode/',
      //  'fileStoreUrl' => './upload/'.date('Y-m-d').'/'.date('H').'/'.date('i').'/'.date('s').'/',
        'allowImgType' => [
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/pjpeg',
            'image/gif',
            'image/bmp',
            'image/x-png' ,
        ],
        'allowFileType' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ],
    ],
    'download' =>[
        'file' =>'./data/file/', //下载文件保存路径
    ],
    'user' => [
        'type' => 1,//用户类型【1C端用户，2B端用户，3财务系统，4运营系统】
        'overduetime' => date('Y-m-d H:i:s', strtotime("+7 day")),//0没有过期时间，date('Y-m-d H:i:s', strtotime("+7 day")),
        'cookieSalt' => '6a204b@d#89f3c834678()(*&^&asdf567*()8afd5c77c717a097a',//登录cookie的加密盐值
        'secret' => '58511cba62024dd8635e98cc80ed4241'
    ],
    'sms' => [
        'overduetime' => 30,//验证码失效时间，单位分钟
        'max_verify_times' => 3,//验证码最大验证次数
        'tencent'=>[
            'host'=>'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400178663&random=',
            'appkey'=>'d789cc2fa6010aeb821d288343143e33',
            'appid'=>'1400178663',
            'templateIdArr' => [
                'handId' => '262456',//操作相关的验证码
                'loginSymId' => '262459',//登录相关的验证码
            ],
        ],
    ],
    //打印机状态的获取接口
    'printer' => [
        'host' => 'http://119.57.117.241:10244/elastic/esQuery',
    ],
    //打印机模型图片
    'printModelImg' => "https://wbaole.oss-cn-beijing.aliyuncs.com/wbaole/15485001267221019841TSqdUrKQ8.png",
    //定时任务队列服务
    'rediskey' =>[
        //支付相关相关
        'pay' => [
            //支付成功后未更新成功支付单号的话通过任务处理
            'notUpdateOrderStatus' => "3d:script:alreadyPayNotUpdateOrderStatus",
            //支付完成的数据写入到支付详情数据里面
            'payDetail' => "3d:script:alreadyPay",
        ],
        //订单相关
        'order' => [
//            'order' => 'tingxihuan:order',//主订单记录key
        ],
        'projectType' => [
            'Imgs' => '3d:script:imgs',
        ],
        //外部产品对应的数据缓存
        'projectData' => [
            //获得参数字典
            'getDic'=>"3d:script:dic",
            //获取库存信息
            //打印机动态数据接口
            'printTypeInfoHost'=>'3d:script:printTypeInfoHost',
        ],
        //用户的认证
        'userAuth' => [
            //邮箱认证
            'email'=>"3d:script:authemail",
            'emailExpire'=> 60*60*24*7,//一小时
        ],
        'user' => [
            'version' => "3d:script:authemail",
        ],
    ],
];
