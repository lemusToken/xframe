<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Config;

/**
 * 配置中心
 * Class Config
 * @package Libs\Config
 */
abstract class Config {

    protected static $config=[];

    /**
     * Config constructor.
     */
    protected function __construct() {
    }

    /**
     * 载入单个配置（文件）
     * @param $name
     * @return mixed
     */
    abstract public function load($name);

    /**
     * 获取配置
     * @param string $key 名称
     * @param bool $recurse 是否递归
     * @return mixed
     */
    abstract public function item($key='',$recurse=false);
}