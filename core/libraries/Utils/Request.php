<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/23
 * Time: 15:59
 */

namespace Libs\Utils;

/**
 * Class Request
 * @package Libs\Utils
 * @method static mixed get() get($key=null,$val=null)
 * @method static mixed post() post($key=null,$val=null)
 * @method static mixed request() request($key=null,$val=null)
 * @method static void clear() clear($type)
 * @method static void clearAll() clearAll($except = array())
 */
class Request {
    private static $instances = array();

    private function __construct() {
    }

    public static function getInstance() {
        return self::$instances?:new self;
    }

    private function _clearAll($except=[]) {
        $temp = [];
        if (!empty($except)) {
            foreach ($except as $v) {
                if (($val=$this->_get($v))!==null) {
                    $temp['get'][$v] = $val;
                }
                if (($val=$this->_post($v))!==null) {
                    $temp['post'][$v] = $val;
                }
                if (($val=$this->_request($v))!==null) {
                    $temp['request'][$v] = $val;
                }
            }
        }
        $this->_clear('request');
        $this->_clear('get');
        $this->_clear('post');
        if (!empty($temp['get'])) {
            foreach ($temp['get'] as $k=>$v) {
                $this->_get($k,$v);
            }
        }
        if (!empty($temp['post'])) {
            foreach ($temp['post'] as $k=>$v) {
                $this->_post($k,$v);
            }
        }
        if (!empty($temp['request'])) {
            foreach ($temp['request'] as $k=>$v) {
                $this->_request($k,$v);
            }
        }
    }

    private function _clear($type) {
        if ($type==='get') {
            $_GET = [];
        }
        elseif ($type==='post') {
            $_POST = [];
        }
        elseif ($type==='request') {
            $_REQUEST = [];
        }
    }

    private function _request($key=null,$val=null) {
        if (empty($key)) return $_REQUEST;
        if (isset($val)) {
            $_REQUEST[$key] = $val;
        }
        elseif (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        return null;
    }

    private function _post($key=null,$val=null) {
        if (empty($key)) return $_POST;
        if (isset($val)) {
            $_POST[$key] = $val;
        }
        elseif (isset($_POST[$key])) {
            return $_POST[$key];
        }
        return null;
    }

    private function _get($key=null,$val=null) {
        if (empty($key)) return $_GET;
        if (isset($val)) {
            $_GET[$key] = $val;
        }
        elseif (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return null;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public static function __callStatic($name, $arguments) {
        return call_user_func_array([self::getInstance(),'_'.$name],$arguments);
    }
}