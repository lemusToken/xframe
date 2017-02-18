<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/23
 * Time: 14:32
 */

namespace Libs\PartitionTable;

/**
 * 分区
 * Class Partition
 * @package Libs\PartitionTable
 */
class Partition extends PartTable {

    public function createTable($table,$data,$checkExist=true){
        $table = $this->_subTablesName($table);

        if (isset($data['config']['part'])) {
            is_string($data['config']['part'])?
                $data['table']['primary'][] = $data['config']['part']:
                $data['table']['primary'] = array_merge($data['table']['primary'],$data['config']['part']);
            $data['table']['primary'] = array_unique($data['table']['primary']);
        }

        $sql = $this->createTableSql($data['fields'],$table,$data['table'],$checkExist);
        $sql = substr($sql,0,strlen($sql)-1);
        $model = '_sub'.$data['config']['mode'];

        return $sql.' '.$this->$model($data['config']).';';
    }

    /**
     * 获取分区表名称
     * @param $table
     * @return string
     */
    private function _subTablesName($table){
        $table = preg_replace('/\[.*?\]/','',$table);
        return preg_replace('/_{2,}|_$/','',$table);
    }

    /**
     * 取余法
     * @param array $data
     * @return string
     */
    private function _subRemainder($data){
        return "PARTITION BY HASH ({$data['part']}) PARTITIONS {$data['total']}";
    }

    /**
     * 周分法
     * @param array $data
     * @return string
     */
    private function _subWeek($data){
        $params = $data['range'];
        $range = explode('-',$params);

        $dataWeek = Calculate::dateRange2week($range);
        $part = [];
        $sql = "PARTITION BY RANGE (YEARWEEK({$data['part']}))(";

        foreach ($dataWeek as $key=>$val){
            $key = str_replace('-','',$key);
            $part[] = "PARTITION p$key VALUES LESS THAN ($key)";
        }
        $sql .= implode(',',$part).')';
        return $sql;
    }

    /**
     * 月分法
     * @param $data
     * @return array
     */
    private function _subMonth($data){
        $params = $data['range'];
        $range = explode('-',$params);

        $part = [];
        $dataMonth = Calculate::dataRange2month($range);
        $sql = "PARTITION BY RANGE (to_days({$data['part']})) ( ";

        foreach ($dataMonth as $key=>$val){
            list($y,$m) = explode('-',$key);
            $m += 1;
            if ($m>12) {
                $m = 1;
                $y += 1;
            }
            $key = str_replace('-','',$key);
            $part[] = "PARTITION p$key VALUES LESS THAN (to_days('$y-$m-01'))";
        }

        $sql .= implode(',',$part).')';
        return $sql;
    }

    /**
     * 年分法
     * @param $data
     * @return string
     */
    private function _subYear($data) {
        $params = $data['range'];
        $range = explode('-',$params);
        $part = [];

        $dataYear = Calculate::dataRange2year($range);

        $sql = "PARTITION BY RANGE (YEAR({$data['part']})) ( ";

        foreach ($dataYear as $key=>$val){
            $gt = $val+1;
            $part[] = "PARTITION p$val VALUES LESS THAN ($gt)";
        }

        $sql .= implode(',',$part).')';
        return $sql;
    }

    /**
     * 按字段分
     * @param $data
     * @return string
     */
    private function _subColumn($data) {
        $params = $data['range'];
        $cols = $data['part'];

        $sql = 'PARTITION BY RANGE COLUMNS('.implode(',',$cols).') (';
        $part = [];

        foreach ($params as $key=>$val){
            if (!empty($val)) {
                foreach ($val as &$v) {
                    $v = is_string($v)?"'$v'":$v;
                }
            }
            $part[] = "PARTITION p$key VALUES LESS THAN (".implode(',',$val).")";
        }

        $sql .= implode(',',$part).')';
        return $sql;
    }
}