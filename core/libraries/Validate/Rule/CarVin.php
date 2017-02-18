<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/1
 * Time: 17:11
 */

namespace Libs\Validate\Rule;
use Libs\Validate\Validate;


/**
 * 判断是否为有效的汽车vin
 * Class CarVin
 * @package Libs\Validate\Rule
 */
class CarVin extends Validate{
    /**
     * 校验车辆VIN编号是否有效
     * <li>使用vin的校验算法，直接计算出vin是否有效</li>
     * @see http://wenku.baidu.com/link?url=Ph3fYPtSmbOFpNAvgNIvLJkbo7SW7XWMuRsgLQ0640wPTvXo0DdfIHcXqHpRDN5JzSgkaVg4uBFbaL5oeYRZWvjjOBSJvuyUn11m_8rpwuK
     * @param string $sVin 车辆的VIN码
     * @return boolean true:校验通过 | false:校验失败
     */
    protected function rule($sVin,$params=[]) {
            static $aCharMap = array(
            '0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,
            'A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6,'G'=>7,'H'=>8,'J'=>1,'K'=>2,
            'L'=>3,'M'=>4,'N'=>5,'P'=>7,'R'=>9,'S'=>2,'T'=>3,'U'=>4,'V'=>5,'W'=>6,
            'X'=>7,'Y'=>8,'Z'=>9
        );
        static $aWeightMap = array(8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2);
        foreach (array_keys($aCharMap) as $sNode){//取出key
            $aCharKeys[] = strval($sNode);
        }
        $sVin = strtoupper($sVin); //强制输入大写
    
        if (strlen($sVin) !== 17){
            return false; //长度不对
        }elseif (!in_array($sVin{8}, array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'X')) ) {
            return false; //校验位的值不对
        }
        //检查vincode字符是否超表
        for ($i=0; $i<17; $i++){
            if (!in_array($sVin{$i}, $aCharKeys)){
                return false; //超出范围
            }
        }
        //计算权值总和
        $iTotal = 0;
        for ($i=0; $i<17; $i++){
            $iTotal += $aCharMap[$sVin{$i}] * $aWeightMap[$i];
        }
        //计算校验码
        $sMode = $iTotal % 11;
        if ($sMode < 10 && $sMode === intval($sVin{8}) ){
            return true;
        }elseif (10 === $sMode && 'X' === $sVin{8}){
            return true;
        }else{
            return false;
        }
    }
}