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
 * 判断是无符号整型
 * Class UnInt
 * @package Libs\Validate\Rule
 */
class UnInt extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^\d+$/',$val);
    }
}