<?php

namespace Libs\Router;

/**
 * 数据存储
 * Class Data
 * @package URL
 * @author xule
 */
class Data{
    //当前路由的数据
    static $data=[];
    private static $_instance;

    /**
     * 写入数据
     * @param $key
     * @param $val
     */
    public function set($key,$val){
        self::$data[$key] = $val;
    }

    /**
     * 获取数据
     * @param $key
     * @return mixed
     */
    public function get($key=null){
        return isset($key)?self::$data[$key]:self::$data;
    }

    /**
     * 单例
     * @return Data
     */
    public static function singleton(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }
}