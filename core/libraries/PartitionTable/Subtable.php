<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/23
 * Time: 14:33
 */

namespace Libs\PartitionTable;

/**
 * 分表
 * Class Subtable
 * @package Libs\PartitionTable
 */
class Subtable extends PartTable{

    /**
     * 创建sql
     * @param $table
     * @param $data
     * @param bool $checkExist
     * @return array
     */
    public function createTable($table,$data,$checkExist=true){
        $segs = $this->_subTablesName($table,$data['config']['mode'],$data['config']);
        $sql = [];

        foreach ($segs as $val){
            $sql[] = $this->createTableSql($data['fields'],$val,$data['table'],$checkExist);
        }
        return $sql;
    }

    /**
     * 更新sql
     * @param $sql
     * @param $data
     * @return mixed
     */
    public function updateTable($sql,$data) {
        return $this->_subTablesName($sql,$data['config']['mode'],$data['config']);
    }

    /**
     * 获取分表名称列表
     * @param $table
     * @param $model
     * @param $config
     * @return mixed
     */
    private function _subTablesName($table,$model,$config){
        $mode = "_sub{$model}";
        return $this->$mode($table,$config);
    }

    /**
     * 取余法获取分表名
     * @param $table
     * @param $data
     * @return array
     */
    private function _subRemainder($table,$data){
        $tables = [];
        $char = '[N]';
        $hasStar = strpos($table,$char)!==false;
        for ($i=0;$i<$data['total'];$i++){
            $tables[] = $hasStar?str_replace($char,$i,$table):$table."_$i";
        }
        return $tables;
    }

    /**
     * 周分法获取分表名
     * @param $table
     * @param $data
     * @return array
     */
    private function _subWeek($table,$data){
        $tables = [];

        $params = $data['range'];
        $range = explode('-',$params);

        $data = Calculate::dateRange2week($range);

        foreach ($data as $key=>$val){
            $tables[] = str_replace(['[Y]','[W]'],explode('-',$key),$table);
        }
        return $tables;
    }

    /**
     * 月分法获取分表名
     * @param $table
     * @param $data
     * @return array
     */
    private function _subMonth($table,$data){
        $tables = [];

        $params = $data['range'];
        $range = explode('-',$params);

        $data = Calculate::dataRange2month($range);

        foreach ($data as $key=>$val){
            $tables[] = str_replace(['[Y]','[M]'],explode('-',$key),$table);
        }
        return $tables;
    }

    /**
     * 按年分
     * @param $table
     * @param $data
     * @return array
     */
    private function _subYear($table,$data){
        $tables = [];

        $params = $data['range'];
        $range = explode('-',$params);

        $data = Calculate::dataRange2year($range);

        foreach ($data as $key=>$val){
            $tables[] = str_replace(['[Y]'],$val,$table);
        }
        return $tables;
    }

    /**
     * 按字段分
     * @param $table
     * @param $data
     * @return array
     */
    private function _subColumn($table,$data) {
        $tables = [];

        $params = $data['range'];

        if (!empty($params)) {
            $p = [
                'key'=>[],
                'val'=>[]
            ];
            foreach ($params as $k=>$v) {
                foreach ($v as $kk=>$vv) {
                    !isset($p['key'][$kk])&&$p['key'][$kk] = '[C'.$kk.']';
                    $p['val'][$k][] = $vv;
                }
            }
            foreach ($p['val'] as $v) {
                $tables[] = str_replace($p['key'],$v,$table);
            }
        }
        return $tables;
    }
}