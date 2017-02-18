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
 * 判断为空，包括空字符
 * Class IsEmpty
 * @package Libs\Validate\Rule
 */
class IsEmpty extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^\s*$/',$val);
    }
}