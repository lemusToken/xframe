<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/10
 * Time: 14:47
 */
namespace Libs\Utils\Singleton;

trait InstanceTrait {
    //缓存实例
    private static $instance=[];
    //实例名称
    private static $singletonKey='';

    /**
     * 单例模式加载实例
     * @param string $key 实例名称
     * @return $this
     */
    public static function loadInst($key='_') {
        self::$singletonKey = $key;
        $class = __CLASS__;
        $inst = isset(self::$instance[$key])?self::$instance[$key]:null;
        $arguments = func_get_args();
        if (empty($inst)) {
            if (empty($arguments)) {
                $inst = self::$instance[$key] = call_user_func([$class,'createInst']);
            }
            else {
                array_shift($arguments);
                $inst = self::$instance[$key] = call_user_func_array([$class,'createInst'],$arguments);
            }
        }
        return $inst;
    }

    /**
     * 生成实例
     * @return $this
     */
    public static function createInst() {
        $arguments = func_get_args();
        $class = __CLASS__;
        $inst = null;
        if (empty($arguments)) {
            $inst = new self;
        }
        else {
            $reflection = new \ReflectionClass($class);
            if ($reflection->getConstructor()) {
                $inst = $reflection->newInstanceArgs($arguments);
            }
            else {
                $inst = $reflection->newInstanceWithoutConstructor();
            }
        }
        return $inst;
    }
}