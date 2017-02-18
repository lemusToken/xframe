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
 * 检查身份证号码是有有效
 * Class IdCardNumber
 * @package Libs\Validate\Rule
 */
class IdCardNumber extends Validate{
    /**
     * 检查身份证号码是有有效（长度18位）
     * <li>使用身份证校验算法</li>
     * @param string $sIdCard
     * @return boolean
     * @see https://zh.wikipedia.org/wiki/%E4%B8%AD%E5%8D%8E%E4%BA%BA%E6%B0%91%E5%85%B1%E5%92%8C%E5%9B%BD%E5%85%AC%E6%B0%91%E8%BA%AB%E4%BB%BD%E5%8F%B7%E7%A0%81
     */
    protected function rule($sIdCard,$params=[]) {
	    $iN = 0; $iSum=0;
	    if (18 !== strlen($sIdCard)){
	        return false;
	    }
        for ($i = 0; $i < 17; $i++){
            $iSum += ((1 << (17 - $i)) % 11) * intval($sIdCard{$i});
        }
        $iN = (12 - ($iSum % 11)) % 11;
        if ($iN < 10){
            return ($iN == intval($sIdCard{17}));
        }else{
            return (strtoupper($sIdCard{17}) == 'X');
        }
    }
}