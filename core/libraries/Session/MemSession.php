<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/13
 * Time: 14:05
 */

namespace Libs\Session;


class MemSession extends Session implements \SessionHandlerInterface{
    private $memcache;

    public function __construct($memcache) {
        parent::__construct();
        $this->memcache = $memcache;
    }

    public function open($save_path, $session_id) {
        $this->savePath = $save_path;
        $this->sessionId = $session_id;
        return true;
    }

    public function close() {
        return true;
    }

    public function read($session_id) {
        return $this->memcache->get($this->sessionId);
    }

    public function write($session_id, $session_data) {
        //存储1天
        $this->memcache->set($this->sessionId,$session_data,$this->expiredTime*60);
    }

    public function destroy($session_id) {
        return (bool)$this->memcache->delete($session_id);
    }

    /**
     * memcache自动清除
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime) {
        return true;
    }

}