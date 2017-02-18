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
 * 判断不含有特殊字符
 * Class NoSpecial
 * @package Libs\Validate\Rule
 */
class NoSpecial extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[\w\x{4e00}-\x{9fa5}]+$/u',$val);
    }
}