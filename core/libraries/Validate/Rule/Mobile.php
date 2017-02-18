<?php
/**
 * User: lijian
 * Date: 2017/01/03
 * Time: 17:11
 */

namespace Libs\Validate\Rule;
use Libs\Validate\Validate;


/**
 * 判断是手机号(根据号段进行区分)
 * Class Mobile
 * @package Libs\Validate\Rule
 */
class Mobile extends Validate{
    /**
     * (non-PHPdoc)
     * @see \Libs\Validate\Validate::rule()
     * @return boolean
     */
    protected function rule($val,$params=[]) {
        static $regex =
            '/^13[0-9]{1}[0-9]{8}$|^14[57]{1}[0-9]{8}$|^15[012356789]{1}[0-9]{8}$|^17[01678]{1}[0-9]{8}$|^18[0-9]{1}[0-9]{8}$/';
        return (preg_match($regex,$val)?true:false);
    }
}