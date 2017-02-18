<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/1
 * Time: 17:46
 */

namespace Libs\Validate;


class Run {

    private static $instance = [];

    /**
     * 验证
     * @param string $name 规则类名
     * @param string $val 值
     * @param array $params 控制参数
     * @return mixed
     */
    public static function check($name,$val,$params=[]) {
        //先判断外部类是否存在
        $class = "\\App\\Libs\\Validate\\Rule\\$name";
        if (empty(self::$instance[$name])&&class_exists($class)) {
            self::$instance[$name] = new $class;
        }
        $class = "\\Libs\\Validate\\Rule\\$name";
        if (empty(self::$instance[$name])&&class_exists($class)) {
            self::$instance[$name] = new $class;
        }
        return self::$instance[$name]->run($val,$params);
    }
}