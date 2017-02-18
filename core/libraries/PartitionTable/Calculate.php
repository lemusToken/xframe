<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/23
 * Time: 11:32
 */

namespace Libs\PartitionTable;


class Calculate {

    /**
     * 字符串转正整数
     * @param $str
     * @return int
     */
    public static function str2int($str){
        return (int)$str;
    }

    /**
     * 字符串转crc
     * @param $str
     * @return int
     */
    public static function str2crc($str){
        return (int)crc32($str);
    }

    /**
     * 日期转所在周
     * @param $time
     * @return string
     */
    public static function date2week($time){
        if (is_string($time)){
            $time = strtotime($time);
        }
        return date('W',$time);
    }


    /**
     * 日期转所在月
     * @param $time
     * @return string
     */
    public function date2month($time){
        if (is_string($time)){
            $time = strtotime($time);
        }
        return date('m',$time);
    }

    /**
     * 日期区间所在周
     * @param array $range
     * @return array
     */
    public static function dateRange2week($range){
        !is_array($range)&&$range=[$range];
        $start = isset($range[0])?strtotime($range[0]):0;
        $end = isset($range[1])?strtotime($range[1]):-1;
        $week = [];

        while (1){
            $w = date('W',$start);
            $y = date('Y',$start)-0;
            $w = $y.'-'.$w;
            if (isset($week[$w][0])){
                $week[$w][1] = $start;
            }
            else{
                $week[$w][0] = $start;
            }
            $start += 3600*24;
            if ($start>$end) break;
        }
        return $week;
    }

    /**
     * 日期区间所在年
     * @param $range
     * @return array
     */
    public static function dataRange2month($range){
        !is_array($range)&&$range=[$range];
        $start = isset($range[0])?strtotime($range[0]):0;
        $end = isset($range[1])?strtotime($range[1]):-1;

        $format = date('Ymd Y m d',$start);
        $data = [];

        list($startData['date'],$startData['year'],$startData['mon'],$startData['day']) = explode(' ',$format);
        $year = $startData['year']-0;

        if ($end<0){
            $key = $year.'-'.($startData['mon']<10?'0'.$startData['mon']:$startData['mon']);
            $data[$key] = [$startData['date']-0];
            return $data;
        }
        $format = date('Ymd Y m d',$end);
        list($endData['date'],$endData['year'],$endData['mon'],$endData['day']) = explode(' ',$format);
        $len = ($endData['year']-$startData['year'])*12+$endData['mon']-$startData['mon']+1;
        $year = $startData['year']-0;
        for ($i=0;$i<$len;$i++){
            $mon = ($i+$startData['mon'])%12;
            $mon===0&&$mon=12;
            if ($mon===1&&($i+$startData['mon'])/12>1){//新的一年
                $year += 1;
                $mon = 1;
            }
            if ($i===0){
                $sd = $startData['date']-0;
            }
            else{
                $sd = $year*10000+$mon*100+1;
            }
            if ($i===$len-1){
                $se = $endData['date']-0;
            }
            else{
                $se = $year*10000+$mon*100+date('t',strtotime($sd));
            }

            $key = $year.'-'.($mon<10?'0'.$mon:$mon);
            $data[$key] = [
                $sd,$se
            ];
        }
        return $data;
    }

    /**
     * 获取区间中所有的年份
     * @param $range
     * @return array
     */
    public static function dataRange2year($range) {
        return range($range[0],$range[1]);
    }
}