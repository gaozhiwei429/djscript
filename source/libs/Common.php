<?php
/**
 * 公共函数类
 * @author jawei
 * @email gaozhiwei429@sina.com
 * @Mobile: 15910987706
 * @Date: 2017-06-06
 * @Copyright: 2018 北京往全保科技有限公司. All rights reserved.
 * 注意：本内容仅限于北京往全保科技有限公司内部传阅，禁止外泄以及用于其他的商业目的
 */
namespace source\libs;
use Yii;
class Common
{
    /**
     * 获取毫秒时间
     * @return float
     */
    public static function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
    /**
     * 正则匹配手机号
     */
    public static function pregPhonNum($phoneNum) {
        //if(preg_match("/^1[0-9]{10}$/",$phoneNum)){//13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}
        #根据支付的手机号校验规则修改
        if(preg_match("/^1[345789]\d{9}$/",$phoneNum)){//13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}
            //验证通过
            return true;
        }else{
            //手机号码格式不对
            return false;
        }
    }

    /**
     * 验证身份证号
     * @param $vStr
     * @return bool
     */
    public static function isCreditNo($idcard) {
        $City = array(11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江",31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",81=>"香港",82=>"澳门",91=>"国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i',$idcard) and!preg_match('/^\d{15}$/i',$idcard)) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($idcard,0,2)),$City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15)
        {
            $sBirthday = '19'.substr($idcard,6,2).'-'.substr($idcard,8,2).'-'.substr($idcard,10,2);
            $sBirthdayTime = strtotime($sBirthday);
            $dd = date('Y-m-d',$sBirthdayTime);
            if($sBirthday != $dd)
            {
                return false;
            }
            $idcard = substr($idcard,0,6)."19".substr($idcard,6,9);//15to18
            $Bit18 = getVerifyBit($idcard);//算出第18位校验码
            $idcard = $idcard.$Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard,6,4);
        if ($year<1900 || $year>2078 )
        {
            return false;
        }
        //18位身份证处理
        $sBirthday = substr($idcard,6,4).'-'.substr($idcard,10,2).'-'.substr($idcard,12,2);
        $sBirthdayTime = strtotime($sBirthday);
        $dd = date('Y-m-d',$sBirthdayTime);
        if($sBirthday != $dd)
        {
            return false;
        }
        return true;
    }


    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function getVerifyBit($idcard_base)
    {
        if(strlen($idcard_base) != 17)
        {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4','3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++)
        {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }


    /**
     * @param $idcard
     * @return bool|string
     * 根据身份证号码获取对应的出生年
     */
    public static function getYearByIdCard($idcard) {
        $idCardLength = strlen($idcard);
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15)
        {
            $sBirthday = '19'.substr($idcard,6,2).'-'.substr($idcard,8,2).'-'.substr($idcard,10,2);
            $sBirthdayTime = strtotime($sBirthday);
            $dd = date('Y-m-d',$sBirthdayTime);
            if($sBirthday != $dd)
            {
                return false;
            }
            $idcard = substr($idcard,0,6)."19".substr($idcard,6,9);//15to18
            $Bit18 = self::getVerifyBit($idcard);//算出第18位校验码
            $idcard = $idcard.$Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard,6,4);
//        if ($year<1900 || $year>2078 )
//        {
//            return false;
//        }
        return $year;
    }

    /**
     *数字金额转换成中文大写金额的函数
     *String Int  $num  要转换的小写数字或小写字符串
     *return 大写字母
     *小数位为两位
     **/
    public static function num_to_rmb($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num)-1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j-3;
                $slen = $slen-3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c)-3, 3) == '零') {
            $c = substr($c, 0, strlen($c)-3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        }else{
            return $c . "整";
        }
    }
    /**
     * @param $str
     * @param int $type 是否包含中文（）
     * @return int
     * 中文匹配
     */
    public static function pregChinese($str,$type=0) {
        if ($type) {
            return preg_match("/^[\x{4e00}-\x{9fa5}|\（|\）]+$/u",$str);
        }
        return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str);
    }

    /**
     * @param $length
     * @return null|string
     * 生成随机字符串
     */
    public static function  getRandChar($length, $is_num=false){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        if($is_num) {
            $strPol = '0123456789';
        }
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 清除中文中空格
     *
     * @return string
     * @author gaozhiwei
     **/
    public static function trimSpace($value)
    {
        $arr = preg_split('/(?<!^)(?!$)/u', $value);
        $new_name = '';
        foreach($arr as $font) {
            if (strlen(trim($font))) {
                $new_name .= $font;
            }
        }
        return $new_name;
    }

    /**
     * 生成密码
     * @param $password
     * @param $salt
     */
    public static function createPassword($password, $salt) {
        $newPwd = md5($password.$salt);
        $newPassword = md5(substr($newPwd,0,10));
        return $newPassword;
    }

    /**
     * 获取当前数据offset开始点
     * @param int $pagesize
     * @param int $page
     * @return int
     */
    public function getOffset($pagesize=10, $page=1) {
        $offset = 0;
        if($pagesize < 0) {
            $offset = 0;
        }
        if($page>=1 && $pagesize>0) {
            $offset = ($page-1) * $pagesize;
        }
        return $offset;
    }
    /**
     * 将数组数据按照id作为索引键值的形式输出
     * @param $dataList
     * @return array
     */
    public static function getIndexDataList($dataList) {
        $dataListArr = [];
        if(!empty($dataList)) {
            foreach($dataList as $datak=>$datav) {
                $dataListArr[$datav['id']] = $datav;
            }
        }
        return $dataListArr;
    }
    /**
     * 实现无限极分类,$items数据结构都一id值为索引
     * @param $items
     * @return array
     */
    public static function generateTree($items){
        $tree = array();
        foreach($items as $item){
            if(isset($items[$item['parent_id']])){
                $items[$item['parent_id']]['son'][] = &$items[$item['id']];
            }else{
                $tree[] = &$items[$item['id']];
            }
        }
        return $tree;
    }

    /**
     * 隐藏字符串的数据
     * @param $string
     * @param $sublen
     * @param int $start
     * @param string $code
     * @return string
     */
    public static function cutStr($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if($code == 'UTF-8')
        {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
            return join('', array_slice($t_string[0], $start, $sublen));
        }
        else
        {
            $start = $start*2;
            $sublen = $sublen*2;
            $strlen = strlen($string);
            $tmpstr = '';
            for($i=0; $i< $strlen; $i++)
            {
                if($i>=$start && $i< ($start+$sublen))
                {
                    if(ord(substr($string, $i, 1))>129)
                    {
                        $tmpstr.= substr($string, $i, 2);
                    }
                    else
                    {
                        $tmpstr.= substr($string, $i, 1);
                    }
                }
                if(ord(substr($string, $i, 1))>129) $i++;
            }
            //if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
            return $tmpstr;
        }
    }
    /**
     * SHA256Hex加密
     * @param $str
     * @return string
     */
    public static function SHA256Hex($str){
        $re=hash('sha256', $str, true);
        return bin2hex($re);
    }
    /**
     * 生成长数字字符串
     * 生成订单号规则
     * @return string
     */
    public function createLongNumberNo($len = 19) {
        $str = "";
        $time = Common::getMillisecond();
        if($len-13>0) {
            $str = substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $len-13);
        }
        $longNo = $time.$str;
        return $longNo;
    }
    /**
     * 递归实现无限极分类
     * @param $array 分类数据
     * @param $pid 父ID
     * @param $level 分类级别
     * @return $list 分好类的数组 直接遍历即可 $level可以用来遍历缩进
     */
    public static function getTree($array){
        //第一步很容易就能看懂，就是构造数据，现在咱们仔细说一下第二步
        //遍历构造的数据
        $treeArr = [];
        foreach($array as $key => &$value){
            $value['son'] = [];
            if((isset($value['id']) && !empty($value['id'])) &&
                (isset($value['parent_id']) && $value['parent_id']==0)
            ){
                $treeArr[$value['id']] = $value;
                $treeArr[$value['id']]['son'] = [];
            }
        }
        foreach($array as $key => $value){
            //如果pid这个节点存在
            if(isset($value['parent_id']) && $value['parent_id']>0){
                //把当前的$value放到pid节点的son中 注意 这里传递的是引用 为什么呢？
                $treeArr[$value['parent_id']]['son'][] = $value;
            }
        }
        return $treeArr;
    }
    /**
     * 检验邮件格式是否正确
     * @param $email
     * @return bool
     */
    public static function verifyEmail($email) {
        $isEmail = false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isEmail = true;
        }
        return $isEmail;
    }
}