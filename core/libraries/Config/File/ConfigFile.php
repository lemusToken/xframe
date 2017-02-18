<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Config\File;
use \Libs\Config\Config;

/**
 * 文件配置中心
 * Class ConfigFile
 * @package Libs\Utils
 */
class ConfigFile extends Config{

    private static $instance=[];
    private static $path = '';
    /**
     * @var \Libs\Utils\Symbol\Symbol
     */
    private $symbol = null;
    private $data=[];
    private $key='';

    protected function __construct($name,$params) {
        parent::__construct();
    }

    /**
     * 加载
     * @param string $name
     * @param array $params
     * @param \Libs\Utils\Symbol\Symbol $symbol
     * @return bool|mixed
     */
    public static function singleton($name,$params,$symbol){
        $key = isset($params['app'])?$params['app'].'/'.$name:$name;
        self::$path =  $symbol->parse(':config',true);
        if (!self::isExist($name)) return false;
        if (!isset(self::$instance[$key])) {
            self::$instance[$key]=new ConfigFile($name,$params);
            self::$instance[$key]->key = $key;
            self::$instance[$key]->symbol = $symbol;
            self::$instance[$key]->load($name);
        }
        return self::$instance[$key];
    }

    /**
     * 判断配置文件是否存在
     * @param $name
     * @return bool
     */
    public static function isExist($name) {
        return file_exists(self::$path."/$name.php");
    }

    /**
     * 载入配置类型
     * @param string $name 配置类型
     * @return $this
     */
    public function load($name){
        $this->data = &self::$config[$this->key][$name];

        self::$config[$this->key][$name] = isset(self::$config[$this->key][$name])?
            self::$config[$this->key][$name]:include self::$path."/$name.php";
        return $this;
    }

    /**
     * 添加配置项
     * @param $key
     * @param $val
     * @return $this
     */
    public function add($key,$val){
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                if (empty($k)) continue;
                $this->data[$k] = $v;
            }
        }
        else {
            $this->data[$key] = $val;
        }
        return $this;
    }

    /**
     * 获取单个配置项
     * @param string|bool $key string:单个配置项；bool:表示获取解析符号后的所有配置
     * @param bool $recurse 是否递归
     * @return string|array
     */
    public function item($key=null,$recurse=false){
        if (is_bool($key)) return $this->allItem($key,$recurse);
        if (empty($key)) return $this->data;
        if (!isset($this->data[$key])) return '';

        if (is_array($this->data[$key])){
            $this->parsePerItem($this->data[$key],$recurse);
            return $this->data[$key];
        }
        return $this->symbol->parse($this->data[$key],true);
    }

    public function allItem($parse=true,$recurse=false) {
        if (empty($parse)) return $this->data;

        is_array($this->data)&&$this->parsePerItem($this->data,$recurse);
        return $this->data;
    }

    private function parsePerItem(&$data,$recurse=false) {
        if ($recurse){
            array_walk_recursive($data,function(&$v){
                $v =  $this->symbol->parse($v,true);
            });
        }
        else{
            array_walk($data,function(&$v){
                $v =  is_array($v)?$v:$this->symbol->parse($v,true);
            });
        }
    }
}