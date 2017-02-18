<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/13
 * Time: 16:20
 */

namespace Libs\Session;


abstract class Session {

    protected $sessionId;
    protected $sessionName='cmsid';
    protected $expiredTime='';

    public function __construct() {
        $this->sessionName = 'cmsid';
        $this->sessionId = $this->getSessionId();
        //默认从ini配置中读取
        $this->expiredTime = $this->getExpiredTime();
        session_name($this->sessionName);
        session_id($this->sessionId);
    }

    abstract public function open($save_path, $session_id);
    abstract public function close();
    abstract public function read($session_id);
    abstract public function write($session_id, $session_data);
    abstract public function destroy($session_id);
    abstract public function gc($maxlifetime);

    /**
     * 设置过期时间(分)
     * @param $minutes
     */
    public function setExpiredTime($minutes) {
        if (empty($minutes)) return;
        $this->expiredTime = $minutes;
        session_cache_expire($this->expiredTime);
    }

    /**
     * 设置session垃圾回收几率
     * @param $p
     * @return bool
     */
    public function setGcProperty($p) {
        if (!isset($p)||is_null($p)||!function_exists('ini_set')) return false;
        if ($p==0) {
            $probability = 0;
            $divisor = 1;
        }
        elseif ($p>=1) {
            $probability = 1;
            $divisor = 1;
        }
        else {
            $k = strpos($p,'.');
            $divisor = pow(10,strlen($p)-$k-1);
            $probability = $divisor*$p;
        }
        ini_set('session.gc_probability',$probability);
        ini_set('session.gc_divisor',$divisor);
        return true;
    }

    private static function createSessionId() {
        return md5(uniqid(mt_rand(), true));
    }

    private function getSessionId() {
        //从cookie中取
        if (!empty($_COOKIE[$this->sessionName])) {
            $sessionId = $_COOKIE[$this->sessionName];
        }
        //从request中取
        elseif(!empty($_REQUEST[$this->sessionName])) {
            $sessionId = $_REQUEST[$this->sessionName];
        }
        else {
            $sessionId = self::createSessionId();
        }
        return $sessionId;
    }

    /**
     * 返回分钟
     * @return int|string
     */
    private function getExpiredTime() {
        $time = 24*60;
        if (!empty($this->expiredTime)) {
            $time = $this->expiredTime;
        }
        elseif (function_exists('get_cfg_var')) {
            $time = get_cfg_var('session.gc_maxlifetime');
        }
        elseif (function_exists('ini_get')) {
            $time = ini_get('session.gc_maxlifetime');
        }
        return $time;
    }

}