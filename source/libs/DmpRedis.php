<?php
/**
 * Redis操作
 * @文件名称: DmpRedis.php
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;
use Yii;

class DmpRedis {
    public  $redis;
    public function __construct() {
        $redisParams = isset(Yii::$app->params['redis']) ? Yii::$app->params['redis'] : [];
        $password = isset($redisParams['password']) ? $redisParams['password'] : '';
//        实例化
        $this->redis = Yii::$app->redis;
        //连接服务器
//        $this->redis->connect($host, $port);
        //授权
        if(!empty($password)) {
            $this->redis->auth($password);
        }
    }
    /**
     * Lpush 命令将一个或多个值插入到列表头部
     * redis的队列存储lpush
     * @param $key
     * @param $val
     */
    public function LpushRedis($key, $val) {
        $flag = false;
        if(!empty($val)) {
            if(is_array($val)) {
                foreach($val as $k=>$v) {
                    $flag = $this->redis->lpush($key, $v);
                }
            } else {
                $flag = $this->redis->lpush($key, $val);
            }

        }
        return $flag;
    }
    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边)
     * redis的队列存储rpush
     * @param $key
     * @param $val
     */
    public function RpushRedis($key, $val) {
        $flag = false;
        if(!empty($val)) {
            if(is_array($val)) {
                foreach($val as $k=>$v) {
                    $flag = $this->redis->rpush($key, $v);
                }
            } else {
                $flag = $this->redis->rpush($key, $val);
            }
        }
        return $flag;
    }
    /**
     * 从list 的尾部删除元素，并返回删除元素
     * redis的队列存储删除头部
     * @param $key
     * @param $val
     */
    public function RpopRedis($key) {
        return $this->redis->rpop($key);
    }
    /**
     * 从 list 的头部删除元素，并返回删除元素
     * redis的队列存储删除尾部
     * @param $key
     * @param $val
     */
    public function LpopRedis($key) {
        return $this->redis->lpop($key);
    }
    /**
     * 删除队列key
     * @param $key
     * @return mixed
     */
    public function LdelRedis($key) {
        return $this->redis->del($key);
    }
    /**
     * 获取某个队列数据的长度
     * @param $key
     * @return mixed
     */
    public function llen($key) {
        return $this->redis->llen($key);
    }
    /**
     * 设置缓存数据
     * @param $key
     * @return mixed
     */
    public function set($key, $val) {
        return $this->redis->set($key, $val);
    }
    /**
     * 获取缓存数据
     * @param $key
     * @return mixed
     */
    public function get($key) {
        return $this->redis->get($key);
    }
    /**
     * range可以获取队列中某一段的数据，例如获取队列中1-10的数据，lrange key 1 10
     * @param $key
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public function lrange($key, $start=1, $end=10) {
        $this->redis = Yii::$app->redis;
        return $this->redis->lrange($key, $start, $end);
    }

    public function __destruct() {
        $this->redis = Yii::$app->redis;
        if($this->redis){
            $this->redis->close();
        }
    }
}
