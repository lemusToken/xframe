<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/10
 * Time: 18:16
 */

namespace Libs\Utils;
use Libs\Config\ConfigManager;
use Libs\Utils\Symbol\Symbol;

/**
 * Common
 * Class Common
 * @package Libs\Utils
 */
class Common {

    const PROV='production';

    /**
     * 是否是生成环境
     * @return bool
     */
    public static function isProv() {
        return Symbol::load()->parse(':env')===self::PROV;
    }

    /**
     * 判断操作系统
     * @return string
     */
    public static function checkOS() {
        $os = 'Linux';
        switch (PHP_OS) {
            case 'WINNT':
                $os = 'win';
                break;
            case 'Darwin':
                $os = 'mac';
                break;
        }
        return $os;
    }

    /**
     * utf8 to gbk
     * @return mixed|string
     */
    public static function chs() {
        $vars = func_get_args();
        if (func_num_args()===1) {
            return mb_convert_encoding($vars[0],'gbk','utf8');
        }
        $str = '';
        array_walk($vars,function($v) use(&$str){
            $str .= mb_convert_encoding($v,'gbk','utf8');
        });
        return $str;
    }

    public static function combineStr() {
        $vars = func_get_args();
        if (func_num_args()===1) {
            return $vars[0];
        }
        return implode('',$vars);
    }

    /**
     * 数组批量编码转化
     * @param $in_charset
     * @param $out_charset
     * @param $arr
     * @return mixed
     */
    public static function char2Char($in_charset,$out_charset,$arr){
        return eval('return '.mb_convert_encoding(var_export($arr,true),$out_charset,$in_charset).';');
    }

    /**
     * 深度查找所有文件夹和文件
     * @param $path
     * @param $data
     */
    public static function scandirDeep($path,&$data) {
        if(is_dir($path)){
            $dp=dir($path);
            while($file=$dp->read()){
                if($file!='.'&& $file!='..'){
                    self::scandirDeep($path.'/'.$file,$data);
                }
            }
            $dp->close();
        }
        if(is_file($path)){
            $data['file'][]=$path;
        }
        else {
            $data['folder'][]=$path;
        }
    }

    /**
     * 匹配字符串的开始和结束字符串，并返回所有匹配到的开始和结束的字符位置数组
     * 开始和结束字符串是成对的，例如匹配左括号和右括号
     * @param $str
     * @param $start
     * @param $end
     * @return array
     */
    public static function matchStartEnd($str,$start,$end) {
        $len = strlen($str);
        $slen = strlen($start);
        $elen = strlen($end);
        $s = 0;

        $ps = [];
        $match = [];

        while (1) {
            if (substr($str,$s,$slen)===$start) {
                $ps[]=$s;
                $s += $slen;
            }
            elseif (substr($str,$s,$elen)===$end) {
                $s += $elen;
                if (!empty($ps)) {
                    $match[] = [array_pop($ps),$s];
                }
            }
            else {
                $s += 1;
            }
            if ($s>=$len) {
                break;
            }
        }
        unset($ps);
        $ps=null;
        return $match;
    }

    /**
     * 判断是否是cli模式
     * @return bool
     */
    public static function isCli() {
        return PHP_SAPI === 'cli';
    }

    /**
     * 判断是否是ajax
     * @return bool
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * 判断是否是post
     * @return bool
     */
    public static function isPost() {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=== 'POST';
    }

    /**
     * 判断是否是get
     * @return bool
     */
    public static function isGet() {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * 生成随机字符串
     * @param int $len 字符长度
     * @param bool|string $withoutConfused  是否去掉混淆字符或者int或者letter
     * @return string
     */
    public static function randStr($len=8,$withoutConfused=false) {
        if ($withoutConfused===true) {
            $s = '2345678abcdefhijkmnprstwxyzABCDEFGHJKMNPQRSTWXYZ';
        }
        elseif ($withoutConfused==='int') {
            $s = '0123456789';
        }
        elseif ($withoutConfused==='letter') {
            $s = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        else {
            $s = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $sLen = strlen($s);
        if ($len===$sLen) {
            return str_shuffle($s);
        }
        elseif ($len<$sLen) {
            return substr(str_shuffle($s),0,$len);
        }
        else {
            $r = '';
            $n = (int)($len/$sLen);
            $c = $len%$sLen;
            for ($i=0;$i<$n;$i+=1) {
                $r .= self::randStr($sLen,$withoutConfused);
            }
            $r .=  self::randStr($c,$withoutConfused);
            return $r;
        }
    }

    /**
     * 判断是否是https
     * @return bool
     */
    public static function isHttps(){
        return ( isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) )
            || ( isset($_SERVER['SERVER_PORT'])&&$_SERVER['SERVER_PORT']=='443' )
            || ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0 );
    }

    public static function currentUrl() {
        $protocol = self::isHttps()?'https:':'http:';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol.'//'.$host.$uri;
    }

    public static function jumpTo($location,$isReferer=false) {
        $location = $isReferer?Url::create($location,['ref'=>self::currentUrl()],'&'):$location;
        header("Location:$location");
        die;
    }
}