<?php
/**
 * 用户相关service
 * @文件名称: PassportService.php
 * @author: jawei
 * @Email: gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2018-12-01
 * @Copyright: 2017 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace appcomponents\modules\passport;
use appcomponents\modules\passport\models\UserInfoModel;
use appcomponents\modules\passport\models\UserLoginTokenModel;
use appcomponents\modules\passport\models\UserModel;
use source\libs\Common;
use source\libs\DmpLog;
use source\libs\DmpRedis;
use source\manager\BaseException;
use source\manager\BaseService;

use \Yii;
class PassportService extends BaseService
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'appcomponents\modules\passport\controllers';

    public function init()
    {
        parent::init();
    }
    /**
     *  生成安全的Token值
     * @param string $uid
     * @param string $token
     * @return string
     */
    public static function createToken($uid = '', $token = '') {
        $seeder = $uid . '|' . $token;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $seeder .= $_SERVER['HTTP_USER_AGENT'];
        }
        $seeder .= Yii::$app->params['user']['cookieSalt'];
        return md5($seeder);
    }
    /**
     * 创建加密sign值
     * @param $userId
     * @param $token
     * @return string
     */
    public function createSign($userId, $token) {
        $secret = Yii::$app->params['user']['secret'];
        $ret = md5(md5(mb_substr($token,0,10,'utf-8').$userId).$secret);
        return BaseService::returnOkData($ret);
    }
    /**
     *检查用户的token的合法性
     * @param $userId
     * @param $token
     * @param int $type
     * @return array
     */
    public function checkUserToken($userId, $token, $type=1) {
        $whereParams = [];
        if(!$userId || !$token || !$type) {
            return BaseService::returnErrData([], 5001, "请求参数异常");
        }
        if($userId) {
            $whereParams[] = ['=', 'user_id', $userId];
        }
        if($type) {
            $whereParams[] = ['=', 'type', $type];
        }

        $userTokenModel = new UserLoginTokenModel();
        $userTokenInfo = $userTokenModel->getInfoByParams($whereParams);
        if(!empty($userTokenInfo)) {
            if(isset($userTokenInfo['overdue_time']) && !empty($userTokenInfo['overdue_time'])) {
                $time = strtotime($userTokenInfo['overdue_time']);
                if($time< time()) {
                    return BaseService::returnErrData($userTokenInfo, 5001, "当前账号登陆状态已过期");
                }
            }
            if(isset($userTokenInfo['token']) && !empty($userTokenInfo['token'])) {
                if($userTokenInfo['token'] != $token) {
                    return BaseService::returnErrData($userTokenInfo, 5001, "当前数据不存在");
                }
                return BaseService::returnOkData($userTokenInfo);
            }
            return BaseService::returnErrData($userTokenInfo, 5001, "当前数据不存在");
        }
        return BaseService::returnErrData($userTokenInfo, 5001, "当前数据不存在");
    }
    /**
     * 验证登陆态是否合法
     * @param $userId
     * @param $token
     * @param $sign
     * @param $type
     * @return array
     */
    public function verifyToken($userId, $token, $sign, $type) {
        $verifySignRet = $this->createSign($userId, $token);
        if(BaseService::checkRetIsOk($verifySignRet)) {
            $verifySign = BaseService::getRetData($verifySignRet);
            if($verifySign != $sign) {
                return BaseService::returnErrData('', 5001, "请求参数无效");
            }
            $ret = $this->checkUserToken($userId, $token, $type);
            if(BaseService::checkRetIsOk($ret)) {
                $userTokenInfo = BaseService::getRetData($ret);
                if(!empty($userTokenInfo)) {
                    if(isset($userTokenInfo['type']) && $userTokenInfo['type'] != $type) {
                        return BaseService::returnErrData('', 5001, "请求参数无效");
                    }
                    if(isset($userTokenInfo['overdue_time']) && !empty($userTokenInfo['overdue_time'])) {
                        if($userTokenInfo['overdue_time'] <= date('Y-m-d H:i:s')) {
                            return BaseService::returnErrData('', 5001, "登陆状态已失效");
                        }
                    }
                    if(isset($userTokenInfo['token']) && $userTokenInfo['token'] != $token) {
                        return BaseService::returnErrData('', 5001, "请求参数token无效");
                    }
                    return BaseService::returnOkData($userTokenInfo);
                }
            }
            return $ret;
        }
        return $verifySignRet;
    }
    /**
     * 生成密码
     * @param $password
     * @param $salt
     */
    public function createPassword($password, $salt) {
        $newPwd = md5($password.$salt);
        $newPassword = md5(substr($newPwd,0,10));
        return $newPassword;
    }
    /**
     * 检查没有加密的密码是否和原密码一致
     * @param $password
     * @param $salt
     * @param $oldPassword
     * @return bool
     */
    /**
     * 检查没有加密的密码是否和原密码一致
     * @param $password
     * @param $salt
     * @param $oldPassword
     * @return bool
     */
    public static function verifyPassword($password, $salt, $oldPassword) {
        $pwd = md5($password.$salt);
        $password = md5(substr($pwd,0,10));
        if($password != $oldPassword) {
            return BaseService::returnErrData(false, 500, "输入密码有误");
        }
        return BaseService::returnOkData($password);
    }
    /**
     * 通过用户名获取用户基本信息
     * @param $username
     * @return mixed
     */
    public function getUserDataInfoByUserName($username) {
        //检查是否是手机号
        $checkMobile = Common::pregPhonNum($username);
        if(!$checkMobile) {
            return BaseService::returnErrData([], 500, '请输入正确个手机号');
        }
        $userModel = new UserModel();
        $params['username'] = trim($username);
        $userData = $userModel->getInfoByParams($params);
        if(!empty($userData)) {
            return BaseService::returnOkData($userData);
        }
        return BaseService::returnErrData($userData, '5002', "该用户没有注册账号");
    }
    /**
     * 通过用户id获取用户账户信息
     * @param $username
     * @return mixed
     */
    public function getUserDataByUserId($id) {
        $userModel = new UserModel();
        $params[] = ['=', 'id', $id];
        $userData = $userModel->getInfoByParams($params);
        if(!empty($userData)) {
            return BaseService::returnOkData($userData);
        }
        return BaseService::returnErrData($userData, '5002', "该用户没有注册账号");
    }
    /**
     * 检查用户名是否存在
     * @param $username
     * @return array
     */
    public function checkUserExist($username) {
        //检查是否是手机号
        $checkMobile = Common::pregPhonNum($username);
        if(!$checkMobile) {
            return BaseService::returnErrData([], 500, '请输入正确个手机号');
        }
        $userModel = new UserModel();
        $params['username'] = trim($username);
        $userData = $userModel->getInfoByParams($params);
        if(!empty($userData)) {
            return BaseService::returnOkData([]);
        }
        return BaseService::returnErrData($userData, '5002', "该用户没有注册账号");
    }
    /**
     * 注册用户
     * @param $username
     * @param $password
     * @param $version 当前系统的版本号
     * @return array
     */
    public function register($username, $password, $source=1, $version="1.0") {
        //检查是否是手机号
        $checkMobile = Common::pregPhonNum($username);
        if(!$checkMobile) {
            return BaseService::returnErrData([], 500, '请输入正确个手机号');
        }
        $ret = $this->getUserDataInfoByUserName($username);
        if(BaseService::checkRetIsOk($ret)) {
            return BaseService::returnErrData([], 500, '当前账号已被注册');
        }
        $salt = Common::getRandChar(6);
        $userData = [
            'username' => $username,
            'salt' => $salt,
            'source' => $source,
            'password' => Common::createPassword($password, $salt),
        ];
        $ret = $this->addUserInfo($userData);
        if(!BaseService::checkRetIsOk($ret)) {
            return BaseService::returnErrData([], 524200, "请重新登录");
        }
        $result = $this->login($username, $password, $source=1);
        if(BaseService::checkRetIsOk($result)) {
//            $versionRet = $this->getUserVersion($username);
//            if(BaseService::checkRetIsOk($versionRet)) {
//                $version = BaseService::getRetData($versionRet);
//                $retData['version'] = $version;
//            } else {
//            }
            $retData['version'] = $version;
            $retData = BaseService::getRetData($result);
            $retData['username'] = $username;
            if($version) {
//                $this->addUserVersion($username, $version);
            }
            return BaseService::returnOkData($retData);
        }
        return BaseService::returnErrData([], 5001, "请重新登录");
    }
    /**
     * 添加账号
     * @param $userData
     * @return array
     */
    public function addUserInfo($userData) {
        try {
            $userModel = new UserModel();
            $user_id = $userModel->addInfo($userData);
            if($user_id) {
                return BaseService::returnOkData($user_id);
            }
            return BaseService::returnErrData([], 550, '注册失败');
        } catch (BaseException $e) {
            @DmpLog::error('passport_adduser_error', $e);
            return BaseService::returnErrData([], 520, '注册失败');
        }
    }
    /**
     * 用户PC登录
     * @param $username
     * @param $password
     * @param $version 当前系统的版本号
     * @return array|mixed
     */
    public function login($username, $password, $source=1, $version="1.0", $device_code="") {
        $type = Yii::$app->params['user']['type'];
        $overduetime = Yii::$app->params['user']['overduetime'];
        //检查是否是手机号
        $checkMobile = Common::pregPhonNum($username);
        if(!$checkMobile) {
            return BaseService::returnErrData([], 500, '请输入正确个手机号');
        }
        $ret = $this->getUserDataInfoByUserName($username);
        if(!BaseService::checkRetIsOk($ret)) {
            return $ret;
        }
        $userInfo = BaseService::getRetData($ret);
        $salt = $userInfo['salt'];//用户密码salt加密盐值
        $passwordVal = Common::createPassword($password, $salt);
        if($passwordVal != $userInfo['password']) {
            return BaseService::returnErrData([], 500, '用户名或者密码错误');
        }
        //保存登录状态
        $token = Common::getRandChar(32);
        $ret = $this->saveLoginToken($userInfo['id'], $token, $type, $overduetime, $source, $device_code);
        if(BaseService::checkRetIsOk($ret)) {
            $result = BaseService::getRetData($ret);
//            $versionRet = $this->getUserVersion($username);
//            if(BaseService::checkRetIsOk($versionRet)) {
//                $version = BaseService::getRetData($versionRet);
//            }
            $result['username'] = $username;
            $result['version'] = $version;
            if($version) {
                $this->addUserVersion($username, $version);
            }
            return BaseService::returnOkData($result);
        }
        return $ret;
    }
    /**
     * 添加版本号缓存
     * @param $username
     * @param string $version
     * @return array
     */
    public function addUserVersion($username, $version="1.0") {
        try{
            $versionRedisKey = Yii::$app->params['rediskey']['user']['version'].":".$username;
            $dmpRedis = new DmpRedis();
            $redisDmp = $dmpRedis->set($versionRedisKey, $version);
            if($redisDmp) {
                return BaseService::returnOkData($redisDmp);
            }

        } catch(BaseException $e) {
            return BaseService::returnErrData([], 530600, "添加版本号失败");
        }
    }
    /**
     * 获取版本号信息
     * @param $username
     * @return array
     */
    public function getUserVersion($username) {
        try{
            $versionRedisKey = Yii::$app->params['rediskey']['user']['version'].":".$username;
            $dmpRedis = new DmpRedis();
            $redisDmp = $dmpRedis->get($versionRedisKey);
            if($redisDmp) {
                return BaseService::returnOkData($redisDmp);
            }
            return BaseService::returnOkData("1.0");
        } catch(BaseException $e) {
            return BaseService::returnOkData("1.0");
        }
    }
    /**
     * 用户登录token值存储表
     * @param $user_id
     * @param $token
     * @param $overdueTimeVel
     * @param $source 登陆来源
     * @param $typeVal 用户类型【1C端用户，2B端用户，3财务系统，4运营系统】
     * @param $device_code 设备号
     * @return mixed
     */
    public function saveLoginToken($user_id, $token, $typeVal=1, $overdueTimeVel = 0, $source =1, $device_code="") {
        $type = $typeVal ? $typeVal : Yii::$app->params['user']['type'];
        $overduetime = $overdueTimeVel ? $overdueTimeVel : Yii::$app->params['user']['overduetime'];
        $userTokenModel = new UserLoginTokenModel();
        $overTime = !empty($overduetime) ? $overduetime : '';
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'source' => intval($source),
            'overdue_time' => $overTime,
            'type' => $type ? $type : 0,
            'device_code' => $device_code ? $device_code : "",
        ];
        $ret = $userTokenModel->addInfo($data);
        $sign = BaseService::getRetData($this->createSign($user_id, $token));
        if($ret) {
            $loginSign = [
                'user_id' => $user_id,
                'token' => $token,
                'sign' => $sign
            ];
            return BaseService::returnOkData($loginSign);
        }
        return BaseService::returnErrData($ret, 5001, "token已失效");
    }
    /**
     * 用户退出登陆
     * 用户登陆状态过期
     * @param $userId
     * @param int $type
     * @return array
     */
    public function logout($userId, $type=0) {
        if(!$type) {
            $type = isset(Yii::$app->params['user']['type']) ? Yii::$app->params['user']['type'] : 0;
        }
        $adminRet = $this->getUserDataByUserId($userId);
        if(BaseService::checkRetIsOk($adminRet)) {
            $userTokenModel = new UserLoginTokenModel();
            $userTokenParams[] = ['=', 'user_id', $userId];
            $userTokenParams[] = ['=', 'type', $type];
            $userTokenInfo = $userTokenModel->getInfoByParams($userTokenParams);
            if(!empty($userTokenInfo)) {
                $id = isset($userTokenInfo['id']) ? $userTokenInfo['id'] : 0;
                if(!empty($id)) {
                    $updateInfo['overdue_time'] = date('Y-m-d H:i:s');
                    $updateUserToken = $userTokenModel->updateInfo($id, $updateInfo);
                    if($updateUserToken) {
                        return BaseService::returnOkData($updateUserToken);
                    }
                }
            }
        }
        return BaseService::returnErrData($userId, 500, '退出异常');
    }
    /**
     * 重置密码
     * @param $user_id
     * @param $password
     * @return array
     */
    public function resetPwd($user_id, $password) {
        if(intval($user_id)<=0 || empty($password)) {
            return BaseService::returnErrData([], 535400, "请求参数异常");
        }
        $salt = Common::getRandChar(6);
        $userData = [
            'salt' => $salt,
            'password' => Common::createPassword($password, $salt),
        ];
        return $this->updateInfoById($user_id, $userData);
    }
    /**
     * 修改用户基本信息
     * @param $user_id
     * @param $updateData
     * @return array
     */
    public function updateInfoById($user_id, $updateData) {
        if(intval($user_id)<=0 || empty($updateData) || !is_array($updateData)) {
            return BaseService::returnErrData([], 537600, "请求参数异常");
        }
        $rest = $this->getUserDataByUserId($user_id);
        if(BaseService::checkRetIsOk($rest)) {
            $userModel = new UserModel();
            $updateUserModelRet = $userModel->updateInfo($user_id, $updateData);
            if($updateUserModelRet) {
                return BaseService::returnOkData([]);
            }
        }
        return BaseService::returnErrData([], 537700, "操作失败");
    }

    /**
     * 通过用户id获取用户基本信息
     * @param $username
     * @return mixed
     */
    public function getUserInfoByUserId($id) {
        $userModel = new UserModel();
        $userModelParams[] = ['=', 'id', $id];
        $userData = $userModel->getInfoByParams($userModelParams);
        if(!empty($userData)) {
            $userData['nickname'] = "";
            $userData['avatar_img'] = \Yii::$app->request->hostInfo."/logo.png";
            $userData['sex'] = 1;
            $userData['birthdate'] = "";
            $userData['email'] = "";
            $userData['qq'] = "";
            $userData['wchat'] = "";
            $userData['is_auth_email'] = 0;
            $userData['is_auth_qq'] = 0;
            $userData['is_auth_wchat'] = 0;
            $userInfoModel = new UserInfoModel();
            $userInfoParams[] = ['=', 'user_id', $id];
            $userInfoData = $userInfoModel->getInfoByParams($userInfoParams);
            if(!empty($userInfoData)) {
                if(isset($userInfoData['nickname']) && !empty($userInfoData['nickname'])) {
                    $userData['nickname'] = !empty($userInfoData['nickname']) ? $userInfoData['nickname'] : $userData['username'];
                }
                if(isset($userInfoData['avatar_img']) && !empty($userInfoData['avatar_img'])) {
                    $userData['avatar_img'] = $userInfoData['avatar_img'];
                }
                if(isset($userInfoData['sex']) && !empty($userInfoData['sex'])) {
                    $userData['sex'] = $userInfoData['sex'];
                }
                if(isset($userInfoData['birthdate']) && !empty($userInfoData['birthdate'])) {
                    $userData['birthdate'] = date('Y-m-d', strtotime($userInfoData['birthdate']));
                }
                if(isset($userInfoData['email']) && !empty($userInfoData['email'])) {
                    $userData['email'] = $userInfoData['email'];
                }
                if(isset($userInfoData['qq']) && !empty($userInfoData['qq'])) {
                    $userData['qq'] = $userInfoData['qq'];
                }
                if(isset($userInfoData['wchat']) && !empty($userInfoData['wchat'])) {
                    $userData['wchat'] = $userInfoData['wchat'];
                }
                if(isset($userInfoData['is_auth_email']) && !empty($userInfoData['is_auth_email'])) {
                    $userData['is_auth_email'] = $userInfoData['is_auth_email'];
                }
                if(isset($userInfoData['is_auth_qq']) && !empty($userInfoData['is_auth_qq'])) {
                    $userData['is_auth_qq'] = $userInfoData['is_auth_qq'];
                }
                if(isset($userInfoData['is_auth_wchat']) && !empty($userInfoData['is_auth_wchat'])) {
                    $userData['is_auth_wchat'] = $userInfoData['is_auth_wchat'];
                }
                return BaseService::returnOkData($userData);
            }
            return BaseService::returnOkData($userData);
        }
        return BaseService::returnErrData($userData, '5002', "该用户没有提交数据");
    }

    /**
     * 根据条件更新用户详情数据模型接口
     * @param $params
     * @param $updateData
     * @return array
     */
    public function updateUserInfoModelByParams($params, $updateData) {
        if(empty($params) || empty($updateData)) {
            return BaseService::returnErrData([], 544100, "请求参数异常");
        }

        $userInfoModel = new UserInfoModel();
        $updateInfoRet = $userInfoModel->updateInfoByParams($params, $updateData);
        if($updateInfoRet) {
            return BaseService::returnOkData($updateInfoRet);
        }
        return BaseService::returnErrData($updateInfoRet, 55200, "更新失败");
    }
    /**
     * 获取用户基本信息数据
     * @param $params
     * @return array
     */
    public function getUserInfoByParams($params) {
        $userInfoModel = new UserInfoModel();
        $userData = $userInfoModel->getInfoByParams($params);
        if(!empty($userData)) {
            return BaseService::returnOkData($userData);
        }
        return BaseService::returnErrData($userData, 544500, "用户详情数据不存在");
    }
    /**
     * 编辑用户基本信息
     * @param $user_id
     * @param $updateData
     * @return array
     */
    public function editInfoDataByUserId($user_id, $updateData) {
        if(intval($user_id)<=0 || empty($updateData) || !is_array($updateData)) {
            return BaseService::returnErrData([], 537600, "请求参数异常");
        }
        $rest = $this->getUserDataByUserId($user_id);
        if(BaseService::checkRetIsOk($rest)) {
            $userInfoModel = new UserInfoModel();
            $userInfoParams['user_id'] = $user_id;
            $getUserInfoRet = $this->getUserInfoByParams($userInfoParams);
            if(BaseService::checkRetIsOk($getUserInfoRet)) {
                return $this->updateUserInfoModelByParams($userInfoParams, $updateData);
            }
            $updateData['user_id'] = $user_id;
            $updateInfoRet = $userInfoModel->addInfo($updateData);
            if($updateInfoRet) {
                return BaseService::returnOkData([]);
            }
            return BaseService::returnErrData($updateInfoRet, 549400, "更新用户基本信息数据失败");
        }
        return BaseService::returnErrData([], 537700, "该用户信息不存在");
    }
    /**
     * 获取用户登录user_token值存储数据
     * @param $params
     * @return array
     */
    public function getUserTokenByparams($params) {
        if(!empty($params)) {
            $userTokenModel = new UserLoginTokenModel();
            $userTokenInfo = $userTokenModel->getInfoByParams($params);
            if(!empty($userTokenInfo)) {
                return BaseService::returnOkData($userTokenInfo);
            }
        }
        return BaseService::returnErrData([], 551000, "当前数据不存在");
    }
    /**
     *检查用户的token的合法性
     * @param $userId
     * @param $token
     * @param int $type
     * @return array
     */
    public function GetUserToken($userId) {
        $whereParams = [];
        if(!$userId) {
            return BaseService::returnOkData([], "请求参数异常");
        }
        if($userId) {
            $whereParams[] = ['=', 'user_id', $userId];
        }
        $type = isset(Yii::$app->params['user']['type']) ? Yii::$app->params['user']['type'] : 0;

        if($type) {
            $whereParams[] = ['=', 'type', $type];
        }

        $userTokenModel = new UserLoginTokenModel();
        $userTokenInfo = $userTokenModel->getInfoByParams($whereParams);
        if(!empty($userTokenInfo)) {
            if(isset($userTokenInfo['overdue_time']) && !empty($userTokenInfo['overdue_time'])) {
                $time = strtotime($userTokenInfo['overdue_time']);
                if($time< time()) {
                    return BaseService::returnOkData([], "当前账号登陆状态已过期");
//                    return BaseService::returnErrData($userTokenInfo, 500328, "当前账号登陆状态已过期");
                }
            }
            if(isset($userTokenInfo['token']) && !empty($userTokenInfo['token'])) {
                $sign = BaseService::getRetData($this->createSign($userId, $userTokenInfo['token']));
                $loginSign = [
                    'user_id' => $userId,
                    'token' => $userTokenInfo['token'],
                    'sign' => $sign,
                ];
                return BaseService::returnOkData($loginSign);
            }
        }
        return BaseService::returnOkData([], "当前数据不存在");
//        return BaseService::returnErrData($userTokenInfo, 563800, "当前数据不存在");
//        else {
//            return $this->loginToUserId($userId);
//        }
    }
    /**
     * 用户PC ID登录
     * @param $username
     * @param $password
     * @return array|mixed
     */
    public function loginToUserId($user_id, $source=1, $platform=2) {
        $type = Yii::$app->params['user']['type'];
        $overduetime = Yii::$app->params['user']['overduetime'];

        $ret = $this->getUserInfoByUserId($user_id);
        if(!BaseService::checkRetIsOk($ret)) {
            return $ret;
        }
        $userInfo = BaseService::getRetData($ret);
        $username = isset($userInfo['username']) ? $userInfo['username'] : '';
        //保存登录状态
        $token = Common::getRandChar(32);
        return $this->saveLoginToken($user_id, $token, $type, $overduetime, $source, $username, $platform);
    }
    /**
     * 扫码登录
     * @param $userid
     * @param $token
     * @param $device_code
     * @param int $source
     * @return array|mixed
     */
    public function qrcodeLogin($userid, $token, $device_code, $source=4) {
        $type = Yii::$app->params['user']['type'];
        $checkoutTokenRet = $this->checkUserToken($userid, $token, $type);
        if(BaseService::checkRetIsOk($checkoutTokenRet)) {
            return $this->saveLoginToken($userid, $token, $type, $overdueTimeVel = 0, $source, $device_code);
        }
        return BaseService::returnErrData([], 567800, "APP登录状态异常请检测");
    }
    /**
     * 通过设备编码获取登录的状态
     * @param $device_code
     * @param int $source
     * @return array
     */
    public function getDeviceCodeLoginStatus($device_code, $source=4) {
        if(!empty($device_code)) {
            $type = Yii::$app->params['user']['type'];
            $params[] = ['=', 'type', $type];
            $params[] = ['=', 'source', $source];
            $params[] = ['=', 'device_code', $device_code];
            $params[] = ['>=', 'overdue_time', date('Y-m-d H:i:s')];
            $ret = $this->getUserTokenByparams($params);
            if(BaseService::checkRetIsOk($ret)) {
                $retData = BaseService::getRetData($ret);
                $data = [
                    'user_id' => isset($retData['user_id']) ? $retData['user_id'] : 0,
                    'token' =>  isset($retData['token']) ? $retData['token'] : "",
                    'source' => intval($source),
                    'overdue_time' => isset($retData['overdue_time']) ? $retData['overdue_time'] : 0,
                    'type' => $type ? $type : 0,
                    'device_code' => $device_code ? $device_code : "",
                ];
                $signRet =  $this->createSign($data['user_id'], $data['token']);
                $sign = BaseService::getRetData($signRet);
                if(empty($sign)) {
                    return BaseService::returnOkData([]);
//                    return BaseService::returnErrData([], 5001, "登录状态异常");
                }
                $data['sign'] = $sign;
                return BaseService::returnOkData($data);
            }
            return BaseService::returnOkData([]);
//            return BaseService::returnErrData([], 5001, "登录状态异常");
        }
        return BaseService::returnOkData([]);
//        return BaseService::returnErrData([], 569700, "请求参数异常");
    }
}
