<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/13
 * Time: 16:20
 */

namespace Libs\Session;


use Libs\Cache\Cache;

class SessionManager {

    public static $prefix='';

    public function __construct($cache,$db,$expiredTime=1440,$gcProperty=null) {
        $handler = '';
        if ($cache&&Cache::checkDriver($cache)) {
            $handler = new MemSession($cache);
        }
        elseif ($db) {
            $handler = new DbSession($db);
        }
        $handler->setExpiredTime($expiredTime);
        $handler->setGcProperty($gcProperty);

        //5.4.33报500 session_set_save_handler($handler, true);
        session_set_save_handler([$handler,'open'],[$handler,'close'],[$handler,'read'],[$handler,'write'],[$handler,'destroy'],[$handler,'gc']);
        register_shutdown_function('session_write_close');
        !isset($_SESSION)&&session_start();

        //session.save_path已经无效
//        session_set_save_handler($handler, true);
//        session_start();
    }

    public static function set($key,$val,$isPrivate=true) {
        $isPrivate&&$key = self::$prefix.$key;
        $_SESSION[$key] = $val;
    }

    public static function get($key,$isPrivate=true) {
        $isPrivate&&$key = self::$prefix.$key;
        return isset($_SESSION[$key])?$_SESSION[$key]:null;
    }
}