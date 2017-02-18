<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2017/01/03
 * Time: 17:11
 */

namespace Libs\Validate\Rule;
use Libs\Validate\Validate;


/**
 * 时间日期检查
 * Class CheckDateTime
 * @package Libs\Validate\Rule
 */
class CheckDateTime extends Validate{
    /**
     * 检查身份证号码是有有效（长度18位）
     * <li>使用身份证校验算法</li>
     * @param string $sVal
     * <li>输入值的格式：yyyy-mm-dd | hh:ii:ss | yyyy-mm-dd hh:ii:ss</li>
     * @param array $params 参数
     * <li>array('date', 'time', 'datetime')；date:检查是否为日期，time:检查是否为时间, datetime:检查是否为日期时间</li>
     * @return boolean
     */
    protected function rule($sVal,$params=[]) {
        if (!is_array($params)){ //控制参数非数组
            throw new \ErrorException('$params 必须为数组'); //抛出错误
        }
        if (in_array('date', $params)){ //日期
            return self::checkDate($sVal);
        }elseif (in_array('time', $params)){ //时间
            return self::checkTime($sVal);
        }elseif (in_array('datetime', $params)){ //日期时间
            $sVal = str_replace('  ', ' ', trim($sVal)); //数据矫正
            if (count(explode(' ', $sVal)) !== 2){ //无效格式
                return false;
            }
            list($sDate, $sTime) = explode(' ', trim($sVal)); //人力日期，时间
            return self::checkDate($sDate) && self::checkTime($sTime);
        }
        return false;
    }
    /**
     * 检查日期是否有效
     * @param string $sDate 日期(格式: yyyy-mm-dd)
     * @return boolean
     * @access public
     */
    static private function checkDate($sDate){
        $aParam = explode('-', trim($sDate));
        if (false !== $aParam && count($aParam) !== 3){ //无效格式
            return false;
        }
        list($iYear, $iMon, $iDay) = $aParam;
        return checkdate(intval($iMon), intval($iDay), intval($iYear));
    }
    /**
     * 检查时间是否有效
     * @param string $sDate 日期(格式: hh:ii:ss)
     * @return boolean
     * @access public
     */
    private function checkTime($sTime){
        $aParam = explode(':', trim($sTime));
        if (false !== $aParam && count($aParam) !== 3){ //无效格式
            return false;
        }
        list($iH, $iI, $iS) = $aParam;
        $iH = intval($iH);
        $iI = intval($iI);
        $iS = intval($iS);
        return ($iH >=0 && $iH <=23) && ($iI >=0 && $iI <=59) && ($iS >=0 && $iS <=59);
    }
    
}