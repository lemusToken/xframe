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
 * 判断utf8字符串长度
 * Class U8Len
 * @package Libs\Validate\Rule
 */
class U8Len extends Validate{


    protected function rule($val,$params=[]) {
        $len = mb_strlen($val,'utf-8');
        $min = min($params);
        $max = max($params);
        return $len>=$min&&$len<=$max;
    }
}