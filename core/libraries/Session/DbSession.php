<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/13
 * Time: 15:16
 */

namespace Libs\Session;


class DbSession extends Session implements \SessionHandlerInterface {
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
    }

    public function open($save_path, $session_id) {
        return true;
    }

    public function close() {
        return true;
    }

    /**
     * 读取session
     * @param string $session_id
     * @return mixed
     */
    public function read($session_id) {
        return $this->db->fetchColumn('SELECT session_data FROM session WHERE session_id = "'.$this->sessionId.'" AND expired_time > "'.date('Y-m-d H:i:s').' limit 1"');
    }

    /**
     * 写session
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data) {
        //判断记录是否存在
        $data = $this->db->fetchAssoc('SELECT id,session_data FROM session WHERE session_id = "'.$this->sessionId.'" limit 1');
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s',strtotime($now.' + '.$this->expiredTime.' minutes'));
        if(!empty($data['id'])&&$data['session_data']!==$session_data) {
            //update
            $this->db->update('session',['session_data'=>$session_data,'expired_time'=>$expires],['id'=>$data['id']]);
        }
        elseif(empty($data['id'])) {
            //insert
            $this->db->insert('session',[
                'session_id'=>$this->sessionId,'session_data'=>$session_data,
                'expired_time'=>$expires,'create_time'=>date('Y-m-d H:i:s')
            ]);
        }
        //false的话，程序会报session.save_path写入失败的警告
        return true;
    }

    /**
     * 当执行session_destroy()
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id) {
        return $this->db->delete('session',['session_id'=>$this->sessionId])?true:false;
    }

    /**
     * 一定几率出发回收机制
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime) {
        $now = time();
        return $this->db->exec('DELETE FROM session where '.$now.'> UNIX_TIMESTAMP(expired_time)')?
            true:false;
    }
}