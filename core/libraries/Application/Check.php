<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/10/28
 * Time: 15:00
 */

namespace Libs\Application;
use Libs\Utils\Request;

/**
 * 控制器控制类参数检查类，参数的初始化定义在路由中
 * Class Check
 * @package Libs\Application
 * @method static mixed getParamName() getParamName($name=null)
 */
class Check {

    private static $namespaceStart = '_op_';
    private static $namespaceEnd = '_';

    public static function __callStatic($name, $arguments) {
        if ($name==='getParamName') {
            return self::format($arguments[0]);
        }
        $name = self::format($name);
        if (isset($arguments[0])) {
            Request::request($name, $arguments[0]);
            Request::get($name, $arguments[0]);
            Request::post($name, $arguments[0]);
            return true;
        }
        return Request::request($name);
    }

    private static function format($name) {
        return self::$namespaceStart.$name.self::$namespaceEnd;
    }
}