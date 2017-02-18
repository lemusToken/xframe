<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/16
 * Time: 14:28
 */

namespace Libs\DBAL;
use Doctrine\DBAL\DriverManager;

class Db {
    private static $instance=[];
    private static $config;
    private $connect;
    private $type;

    private function __construct($type) {}

    /**
     * 配置添加
     * @param $config
     */
    public static function setConfig($config) {
        self::$config = $config;
    }

    /**
     * 连接实例
     * @param bool $reconnect 是否重新连接数据库，有时候可能会存在连接时间过长而超时
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function connect($reconnect=false){
        if (!$this->connect) {
            $this->connect = DriverManager::getConnection(self::$config[$this->type]);
        }
        if ($reconnect&&!$this->connect->ping()) {
            $this->connect->close();
            $this->connect = DriverManager::getConnection(self::$config[$this->type]);
        }
        return $this->connect;
    }

    /**
     * 获取dbal实例
     * @param string $type
     * @return $this
     * @throws \ErrorException
     */
    public static function load($type=''){
        if (empty($type)&&!empty(self::$config['default'])) $type=self::$config['default'];

        if (!isset(self::$config[$type])) throw new \ErrorException("数据库配置中，$type 不存在");

        if (empty(self::$instance[$type])) {
            self::$instance[$type]=new self($type);
            self::$instance[$type]->type = $type;
        }
        return self::$instance[$type];
    }
}