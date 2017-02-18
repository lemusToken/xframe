<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/27
 * Time: 16:26
 */

namespace Libs\Cookie;


class CookieManager {

    public static function set($key,$val,$time=0,$options=[]) {
        $path = empty($options['path'])?'/':$options['path'];
        $domain = empty($options['domain'])?null:$options['domain'];
        $secure = empty($options['secure'])?null:$options['secure'];
        $httpOnly = empty($options['httpOnly'])?null:$options['httpOnly'];
        return setcookie($key,$val,time()+$time,$path,$domain,$secure,$httpOnly);
    }

    public static function get($key) {
        return isset($_COOKIE[$key])?$_COOKIE[$key]:null;
    }
}