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
    protected $redis_db = 0; //数据库号
    protected $hash_prefix = 'dangjian_data'; //前缀名称

    public function __construct($hash_prefix='',$db=0) {
//        $redisParams = isset(Yii::$app->params['redis']) ? Yii::$app->params['redis'] : [];
//        $host = isset($redisParams['hostname']) ? $redisParams['hostname'] : '127.0.0.1';
//        $port = isset($redisParams['port']) ? $redisParams['port'] : '6379';
//        $password = isset($redisParams['password']) ? $redisParams['password'] : '';
        //实例化
        $this->redis = Yii::$app->redis;
        //连接服务器
//        $this->redis->connect($host, $port);
        //授权
        if(!empty($password)) {
            $this->redis->auth($password);
        }
        if($db != '') $this->redis_db = $db;
        if($hash_prefix != '') $this->hash_prefix = $hash_prefix;
        $this->redis->select($this->redis_db);
    }
    /*
     * 添加记录
     * @param $id id
     * @param $data hash数据
     * @param $hashName Hash 记录名称
     * @param $SortName Redis SortSet 记录名称
     * @param $redis Redis 对象
     * @return bool
     */
    public function set_redis_page_info($id,$data){
        if(!is_numeric($id) || !is_array($data)) return false;
        $hashName = $this->hash_prefix.'_'.$id;
        $this->redis->hMset($hashName, $data);
        $this->redis->zAdd($this->hash_prefix.'_sort',$id,$id);
        return true;
    }
    /*
     * 获取分页数据
     * @param $page 当前页数
     * @param $pageSize 每页多少条
     * @param $hashName Hash 记录名称
     * @param $SortName Redis SortSet 记录名称
     * @param $redis Redis 对象
     * @param $key 字段数组 不传为取出全部字段
     * @return array
     */
    public function get_redis_page_info($page,$pageSize,$key=array()){
        if(!is_numeric($page) || !is_numeric($pageSize)) return false;
        $limit_s = ($page-1) * $pageSize;
        $limit_e = ($limit_s + $pageSize) - 1;
        $range = $this->redis->ZRANGE($this->hash_prefix.'_sort',$limit_s,$limit_e); //指定区间内，带有 score 值(可选)的有序集成员的列表。
        $count = $this->redis->zCard($this->hash_prefix.'_sort'); //统计ScoreSet总数
        $pageCount = ceil($count/$pageSize); //总共多少页
        $pageList = array();
        foreach($range as $qid){
            if(count($key) > 0){
                $pageList[] = $this->redis->hMGet($this->hash_prefix.'_'.$qid,$key); //获取hash表中所有的数据
            }else{
                $pageList[] = $this->redis->hGetAll($this->hash_prefix.'_'.$qid); //获取hash表中所有的数据
            }
        }
        $data = array(
            'dataList'=>$pageList, //需求数据
            'count'=>$count, //记录总数
            'page'=>$page, //当前页数
            'pageSize'=>$pageSize, //每页多少条
            'pageCount'=>$pageCount //总页数
        );
        return $data;
    }
    /*
     * 删除记录
     * @param $id id
     * @param $hashName Hash 记录名称
     * @param $SortName Redis SortSet 记录名称
     * @param $redis Redis 对象
     * @return bool
     */
    public function del_redis_page_info($id){
        if(!is_array($id)) return false;
        foreach($id as $value){
            $hashName = $this->hash_prefix.'_'.$value;
            $this->redis->del($hashName);
            $this->redis->zRem($this->hash_prefix.'_sort',$value);
        }
        return true;
    }
    /*
     * 清空数据
     * @param string $type db:清空当前数据库 all:清空所有数据库
     * @return bool
     */
    public function clear($type='db'){
        if($type == 'db'){
            $this->redis->flushDB();
        }elseif($type == 'all'){
            $this->redis->flushAll();
        }else{
            return false;
        }
        return true;
    }
    /**
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
     * redis的队列存储删除头部
     * @param $key
     * @param $val
     */
    public function RpopRedis($key) {
        return $this->redis->rpop($key);
    }
    /**
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
}
