<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/24
 * Time: 10:15
 */

namespace Libs\PartitionTable;


class Create {

    private static $config = null;

    public static function setConfig($config) {
        self::$config = $config;
    }

    public static function getConfig() {
        return self::$config;
    }

    /**
     * 生成分表sql或者分表
     * @param string $type 分表类型
     * @return array
     */
    public static function createSql($type){
        $class = null;
        if ($type==='subtable') {
            $class = new Subtable();
        }
        elseif ($type==='partition') {
            $class = new Partition();
        }
        $config = self::getConfig();
        $sql = [];
        foreach ($config as $table=>$cfg) {
            $s = $class->createTable($table,$cfg,true);
            $sql[] = $s;
        }
        return $sql;
    }

    /**
     * 更新分表
     * @param string $sql sql语句
     * @return array
     */
    public static function updateSql($sql) {
        $class = new Subtable();
        $config = self::getConfig();
        preg_match('/(?:table|TABLE)(.*?\[.*?\])/',$sql,$match);
        $table = trim(str_replace('`','',$match[1]));
        $config = $config[$table];
        return $class->updateTable($sql,$config);
    }
}