<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Utils\Symbol;

/**
 * 解析符号
 * Class Symbol
 * @package Libs\Utils\Symbol
 */
class Symbol {

    public static $app='';
    //符号词典
    private $map=[];
    //是否需要缓存
    private $cacheNeed = false;
    //缓存是否可用
    private $cacheEnable = false;
    //词库
    private $words = [];

    //缓存
    private $cache=null;
    //缓存实例
    private static $instance=[];
    //缓存解析结果
    private static $final=[];

    private function __construct() {

    }

    /**
     * 加载
     * @param $cache
     * @return Symbol|null
     */
    public static function load($cache=null) {
        $key = self::getApp();
        if (!isset(self::$instance[$key])) {
            self::$instance[$key]=new Symbol();
        }
        if ($cache&&!self::$instance[$key]->cache) {
            self::$instance[$key]->cache = new SymbolCache($cache);
            self::$instance[$key]->cacheEnable = !!self::$instance[$key]->cache;
        }
        return self::$instance[$key];
    }

    public function init() {
        $this->words = [];
        $this->cacheNeed = false;
    }

    /**
     * 临时添加符号到符号表(同一符号只能添加一次，不可被覆盖)
     * @param string|array $key 单个符号或者符号数组
     * @param string $val 对应值
     * @return $this
     */
    public function add($key,$val=null) {
        $args = func_get_args();
        if (!$args[0]) return false;
        //数组
        if (is_array($args[0])) {
            $this->words[] = ['array',$args[0]];
        }
        //词库文件
        elseif (is_string($args[0])&&!isset($args[1])) {
            $this->words[] = ['lex',$args[0]];
            $this->cacheNeed = true;
        }
        //单项添加
        else {
            $this->words[] = ['word',$args[0],$args[1]];
        }
        return $this;
    }

    /**
     * 保存符号词典(只保存词库)
     */
    public function save() {
        $lexCache = $this->cacheEnable?$this->cache->get():[];
        $cacheUpdate = false;
        if (empty($lexCache)) {
            $lexCache = [];
        }
        elseif ($this->cacheNeed) {
            $cacheUpdate = $this->cache->checkFile(array_keys($lexCache));
        }

        if ($this->words) {
            foreach ($this->words as $val) {
                //如果词库文件的数据缓存存在并且无需更新
                if (!$cacheUpdate&&$this->cacheEnable&&$val[0]==='lex'&&isset($lexCache[$val[1]])) {
                    $val[0] = 'array';
                    $val[1] = $lexCache[$val[1]];
                    $val[2] = null;
                }
                $code ='add'.ucfirst($val[0]);
                $val[2] = isset($val[2])?$val[2]:'';
                $words = $this->$code($val[1],$val[2]);
                if ($this->cacheEnable&&$val[0]==='lex') {
                    $lexCache[$val[1]] = $words;
                }
            }
        }
        if ($cacheUpdate&&!empty($lexCache)) {
            $this->cache->save($lexCache);
        }
        $this->init();
    }

    /**
     * 符号是否存在符号词典
     * @param $key
     * @return bool
     */
    public function exist($key) {
        return isset($this->map[$key]);
    }

    /**
     * 输出符号表
     * @return array
     */
    public function printMap(){
        return $this->map;
    }

    /**
     * 解析符号
     * @param string $str 输入的字符串
     * @param boolean $openCache 是否开启缓存
     * @param array $map 符号词典
     * @return string
     */
    public function parse($str,$openCache=false,$map=[]) {
        //如果是布尔值直接返回
        if (is_bool($str)) return $str;
        //有缓存则直接返回
        if ($openCache&&$cache = $this->cacheMem($str)) {
            return $cache;
        }
        $map = $map?:$this->map;
        //词典为空或者不存在符号直接返回
        if (empty($map)||strpos($str,':')===false) return $str;
        //记录递归结果，防止死循环
        static $all=[];
        $len = strlen($str);
        //操作符
        $matchStr = '';
        $result = '';
        //边界条件
        $match = [
            //单边界条件
            ':'=>false,
            //双边界条件
            '{}'=>false
        ];
        //是否再次解析
        $again = false;

        //字符串替换
        $func = function(&$matchStr,$append='') use ($map,&$result,&$again) {
            if ($matchStr) {
                !$again&&$again = isset($map[$matchStr])&&strpos($map[$matchStr],':')!==false;
                $result .= isset($map[$matchStr])?$map[$matchStr]:':'.$matchStr;
                $matchStr = '';
            }
            $result .= $append;
        };

        for ($i=0;$i<$len;$i++) {
            //双边匹配的开始字符，上一次匹配的结束字符
            if (isset($str{$i+1})&&$str{$i}.$str{$i+1}==='{:') {
                $match['{}'] = true;
                $match[':'] = true;
                $i += 1;
                $func($matchStr);
            }
            //转义代表普通: ，同时匹配结束
            elseif (isset($str{$i+1})&&$str{$i}.$str{$i+1}==='\:') {
                $match[':'] = false;
                $match['{}'] = false;
                $i += 1;
                $func($matchStr,':');
            }
            //:/不做解析，同时匹配结束
            elseif (isset($str{$i+1})&&$str{$i}.$str{$i+1}===':/') {
                $match[':'] = false;
                $match['{}'] = false;
                $i += 1;
                $func($matchStr,':/');
            }
            //单边匹配的开始字符，上一次匹配的结束字符
            elseif ($str{$i}===':') {
                $match[':'] = true;
                $func($matchStr);
            }
            //单边匹配结束
            elseif (in_array($str{$i},[' ','/','?','#'])) {
                $match[':'] = false;
                $func($matchStr,$str{$i});
            }
            //双边匹配结束
            elseif ($match['{}']&&$str{$i}==='}') {
                $match[':'] = false;
                $func($matchStr);
            }
            //边界成立
            elseif ($match[':']) {
                $matchStr .= $str[$i];
            }
            //其余字符串
            else{
                $result .= $str[$i];
            }
        }
        $func($matchStr);

        //当有重复结果时，直接返回
        if (in_array($result,$all)){
            $all = [];
            return $result;
        }

        $all[] = $result;

        //继续解析
        if ($again) {
            $result = $this->parse($result,$map);
        }

        return $openCache?$this->cacheMem($str,$result):$result;
    }

    public function tree2path($tree,$strPrev='') {
        $result = array();
        foreach ($tree as $k => $node) {
            $str = $strPrev==='' ? $k : $strPrev.'.'.$k;
            if (is_array($node)) {
                $arr = $this->tree2path($node, $str);
                $result = array_merge($result, $arr);
            } else {
                $result[$str] = $node;
            }
        }
        return $result;
    }

    /**
     * 缓存结果
     * @param $str
     * @param $result
     * @return mixed
     */
    private function cacheMem($str,$result=null){
        $key = self::getApp();
        return isset(self::$final[$key][$str])?self::$final[$key][$str]:self::$final[$key][$str] = $result;
    }



    /**
     * 添加词库
     * @param $file
     * @return array
     */
    private function addLex($file) {
        $result = [];
        if (file_exists($file)&&$data = include_once $file){
            $result = $this->addArray($data);
        }
        return $result;
    }

    /**
     * 添加数组数据
     * @param $ary
     * @return array
     */
    private function addArray($ary) {
        $result = [];
        if (!is_array($ary)) return $result;
        foreach ($ary as $k=>$v) {
            if (empty($k)) continue;
            if (is_array($v)) {
                $l = $this->tree2path($v);
                if ($l) {
                    foreach ($l as $kk=>$vv) {
                        $kk = $k.'.'.$kk;
                        if (isset($this->map[$kk])) continue;
                        $this->map[$kk] = $this->parse($vv,true);
                    }
                }
            }
            elseif (!isset($this->map[$k])){
                $result[$k] = $this->map[$k] = $this->parse($v,true);
            }
        }
        return $result;
    }

    /**
     * 添加单个符号
     * @param $key
     * @param $val
     * @return string
     */
    private function addWord($key,$val) {
        $result = '';
        if (isset($this->map[$key])) return $result;
        return $this->map[$key] = $this->parse($val,true);
    }

    private static function getApp() {
        return empty(self::$app)?'_':self::$app;
    }
}