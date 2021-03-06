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
 * 判断是(带有符号)整型
 * Class IsInt
 * @package Libs\Validate\Rule
 */
class IsInt extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[+-]?\d+$/',$val);
    }
}