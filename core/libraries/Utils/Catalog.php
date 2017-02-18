<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/10
 * Time: 18:16
 */

namespace Libs\Utils;

/**
 * Framework Catalog
 * Class Catalog
 * @package Libs\Utils
 */
class Catalog {
    private static $list = [
        //控制目录
        'bootstrap'=>':app/bootstrap',
        //资源目录
        'resources'=>':app/resources',
        //缓存目录
        'cache'=>':app/cache',
        //配置目录
        'config'=>':app/config',
        //日志目录
        'logs'=>':app/logs',
        //核心库
        'libs'=>':app/libs'
    ];

    /**
     * 框架目录
     * @param string $name
     * @return string
     */
    public static function path($name='') {
        return $name&&self::$list[$name]?self::$list[$name]:self::$list;
    }
}