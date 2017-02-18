<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:39
 */

namespace Libs\TwigExtend\Extend;


abstract class Extend {

    protected $name = '';
    protected $extendType = '';
    protected $twig=null;
    protected static $config;
    private static $instance=null;
    private static $helper=[];


    /**
     * 生成twig实例
     * Extend constructor.
     */
    protected function __construct() {
        $this->twig = self::singleton();
    }

    /**
     * 设置配置
     * @param $config
     */
    public static function setConfig($config) {
        self::$config = $config;
    }

    /**
     * 获取配置
     * @return mixed
     */
    public static function getConfig() {
        return self::$config;
    }

    /**
     * 添加帮助类实例
     * @param $name
     * @param $helper
     */
    public static function addHelper($name,$helper) {
        self::$helper[$name] = $helper;
    }

    /**
     * 根据名称获取帮助实例
     * @param $name
     * @return mixed
     */
    public static function getHelper($name) {
        return self::$helper[$name];
    }

    /**
     * 加载实例
     * @return \Twig_Environment
     */
    public static function singleton(){
        return self::$instance?:self::$instance=self::getTwig();
    }

    /**
     * 生成twig实例
     * @return \Twig_Environment
     */
    private static function getTwig(){
        $config = self::getConfig();
        $loader = new \Twig_Loader_Filesystem($config['views.files.path']);
        return new \Twig_Environment($loader, array(
            'cache' => $config['views.cache.path'],
            'auto_reload'=>true,
            'charset'=>'utf-8'
        ));
    }

    /**
     * 注册方法
     */
    public function register() {
        $this->add($this->name,$this->fn());
        $helperList = $this->registerHelper();
        if ($helperList) {
            foreach ($helperList as $n=>$f) {
                self::addHelper($this->extendType.'/'.$n,$f);
            }
        }
    }

    protected function registerHelper() {
        return null;
    }

    /**
     * 添加扩展方法
     * @return mixed
     */
    abstract protected function add();

    abstract protected function fn();
}