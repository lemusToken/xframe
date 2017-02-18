<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/10
 * Time: 16:42
 */

namespace Libs\TwigExtend;
use Libs\TwigExtend\Extend\Extend;

/**
 * Twig所有扩展
 * Class ExtendAll
 * @package Libs\TwigExtend
 */
class ExtendAll {

    private static $instance;

    private function __construct() {
        //外部扩展
        $this->addExternal();
        //内部扩展
        //外部扩展和内部扩展如果名称相同，则后者覆盖前者
        $this->addInternal();
    }

    /**
     * 设置配置
     * @param $config
     */
    public static function setConfig($config) {
        Extend::setConfig($config);
    }

    /**
     * 添加帮助类
     * @param $name
     * @param $helper
     */
    public static function addHelper($name,$helper) {
        Extend::addHelper($name,$helper);
    }

    /**
     * 获取实例
     * @return \Twig_Environment
     */
    public static function load($autoload=true) {
        if (!self::$instance) {
            self::$instance=new self();
        }
        $inst = Extend::singleton();
        if ($autoload) {
            $inst->enableAutoReload();
        }
        else {
            $inst->disableAutoReload();
        }
        return $inst;
    }

    private function addInternal() {
        //filter
        $fn = new Filter\Path;
        $fn->register();
        $fn = new Filter\StaticLine;
        $fn->register();
        //function
        $fn = new Functions\Alert;
        $fn->register();
        $fn = new Functions\Load;
        $fn->register();
        $fn = new Functions\_LoadData;
        $fn->register();
        $fn = new Functions\Typeof;
        $fn->register();
        $fn = new Functions\Request;
        $fn->register();
        //tag
        $fn = new Tag\Dev;
        $fn->register();
        $fn = new Tag\Pro;
        $fn->register();
        $fn = new Tag\Load;
        $fn->register();

        $fn = null;
    }

    private function addExternal() {
        $class = "\\App\\Libs\\Twig\\Register";
        if (!class_exists($class)) {
            return false;
        }
        new $class;
        return true;
    }
}