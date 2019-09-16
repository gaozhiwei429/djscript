<?php
/**
 * 图片上传
 * @文件名称: Upload.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use source\manager\BaseService;
use Yii;

class Upload
{
    /**
     * 文件上传
     * @param $files file
     * @return array
     */
    public static function uploadImg($files) {
        $images = [];
        if(isset($files['files']) && !empty($files['files'])) {
            $maxNum = Yii::$app->params['upload']['allowUploadNum'];
            $count = count($files['files']['name']);
            if($count > $maxNum) {
                return BaseService::returnErrData([], 500, '每次最多可上传'.$maxNum.'张图片！');
            }
            $allowImgType = Yii::$app->params['upload']['allowImgType'];
            foreach($files['files']['type'] as $k=>$v) {
                if(!in_array($v, $allowImgType)) {
                    return BaseService::returnErrData([], 500, '上传文件类型不支持');
                }
            }
            $allowSize = Yii::$app->params['upload']['allowSize'];
            $allowUploadSize = $allowSize * 1024 * 1024;
            foreach($files['files']['size'] as $k=>$v) {
                if($v > $allowUploadSize) {
                    return BaseService::returnErrData([], 500, '文件最大支持上传'.$allowSize."M");
                }
            }
            $uploadDir = Yii::$app->params['upload']['fileStoreUrl'];
//            if(!is_dir($uploadDir)) {
//                mkdir($uploadDir, 0777);
//            }
            if(!file_exists($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            foreach($files['files']['tmp_name'] as $k=>$v) {
                $extensionName = substr($files['files']['name'][$k], strrpos($files['files']['name'][$k], '.')+1);
                $fileName = Common::getRandChar(18).'.'.$extensionName;
                $filePath = $uploadDir.$fileName;
                $isBool = @move_uploaded_file($v, $filePath);
                if($isBool) {
                    $images[] = $filePath;//substr($filePath,1);//去掉第一个点
                }
            }
            return BaseService::returnOkData($images);
        }
        return BaseService::returnErrData($images, 5057, "文件上传异常");
    }
    /**
     * 腾讯cos文件上传
     * @param $local_path
     * @param $key
     */
    public function uploadTencentCos($local_path, $key) {
            $name =  Yii::$app->params['upload']['tencentCos']['name'];
            $secretId =  Yii::$app->params['upload']['tencentCos']['secretId'];
            $secretKey =  Yii::$app->params['upload']['tencentCos']['secretKey'];
            $appid =  Yii::$app->params['upload']['tencentCos']['appid'];
            $bucket = "$name-$appid";
            $cosClient = new Client(array(
                'region' => 'ap-beijing', #COS_REGION地域，如ap-guangzhou,ap-beijing-1
                'credentials' => array(
                    'secretId' => $secretId,
                    'secretKey' => $secretKey,
                ),
            ));
            ### 上传文件流
            try {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => fopen($local_path, 'rb')
                ));
                print_r($result);
            } catch (\Exception $e) {
                echo($e);
            }
    }
    /**
     * 阿里oss文件上传
     * @param $local_path
     * @param $key
     */
    public function uploadAliCos($local_path, $key) {
        $bucket =  Yii::$app->params['upload']['aliyunOss']['bucket'];
        $dir =  Yii::$app->params['upload']['aliyunOss']['dir'];
        $accessKeyId =  Yii::$app->params['upload']['aliyunOss']['secretId'];
        $accessKeySecret =  Yii::$app->params['upload']['aliyunOss']['secretKey'];
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://oss-cn-beijing.aliyuncs.com";
        $longNo = Common::createLongNumberNo(19);
        $object = $dir."/".$longNo.$key;
        $uploadFile = $local_path;
        /**
         *  步骤1：初始化一个分片上传事件，获取uploadId。
         */
        try{
//            $options = array(
//                OssClient::OSS_CHECK_MD5 => true,
//                OssClient::OSS_PART_SIZE => 1,
//            );
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

//            $uploadId = $ossClient->multiuploadFile($bucket, $object, $local_path, $options);
            //返回uploadId，它是分片上传事件的唯一标识，您可以根据这个ID来发起相关的操作，如取消分片上传、查询分片上传等。
            $uploadId = $ossClient->initiateMultipartUpload($bucket, $object);
        } catch(OssException $e) {
            return BaseService::returnErrData($e, 518200, $e->getMessage());
        }
        /*
         * 步骤2：上传分片。
         */
        $partSize = 10 * 1024 * 1024;
        $uploadFileSize = filesize($uploadFile);
        $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $responseUploadPart = array();
        $uploadPosition = 0;
        $isCheckMd5 = true;
        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (integer)$piece[$ossClient::OSS_SEEK_TO];
            $toPos = (integer)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                $ossClient::OSS_PART_NUM => ($i + 1),
                $ossClient::OSS_SEEK_TO => $fromPos,
                $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            // MD5校验。
            if ($isCheckMd5) {
                $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            try {
                // 上传分片。
                $responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
            } catch(OssException $e) {
                return BaseService::returnErrData($e, 518200, $e->getMessage());
            }
        }
        // $uploadParts是由每个分片的ETag和分片号（PartNumber）组成的数组。
        $uploadParts = array();
        foreach ($responseUploadPart as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }
        /**
         * 步骤3：完成上传。
         */
        try {
            // 在执行该操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            $completeSource = $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
            $url = "";
            if(isset($completeSource['info']['url'])) {
                $urlData = explode('?', $completeSource['info']['url']);
                if(isset($urlData[0]) && !empty($urlData[0])) {
                    $url = $urlData[0];
                }
            }
            if(!empty($url)) {
                return BaseService::returnOkData($url);
            }
            return BaseService::returnErrData([], 518200, "分片上传异常");
        }  catch(OssException $e) {
            return BaseService::returnErrData($e, 518200, $e->getMessage());
        }
    }
    /**
     * 上传文件到七牛
     * @param $filePath
     * @throws \Exception
     */
    public function uploadQiNiu($filePath) {
        if(!empty($filePath)) {
            // 构建鉴权对象
            $accessKey = Yii::$app->params['upload']['qiniu']['accessKey'];
            $secretKey = Yii::$app->params['upload']['qiniu']['secretKey'];
            $bucket = Yii::$app->params['upload']['qiniu']['bucket'];
            $http = Yii::$app->params['upload']['qiniu']['http'];
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 生成上传 Token
            $token = $auth->uploadToken($bucket);
            // 上传到七牛后保存的文件名
            $key = substr($filePath,2);//去掉第一个点
//            $key = $filePath;
            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传。
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
//            echo "\n====> putFile result: \n";
            if ($err !== null) {
//                var_dump($err);
                return BaseService::returnErrData($err, 500, "上传服务异常");
            } else {
                if(isset($ret['key'])) {
                    $ret['key'] = $http.$ret['key'];
                }
                return BaseService::returnOkData($ret);
//                var_dump($ret);
            }
        }
        return BaseService::returnErrData($filePath, 500, "上传七牛文件异常");
    }
}
