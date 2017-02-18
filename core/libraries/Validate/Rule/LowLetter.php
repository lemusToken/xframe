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
 * 判断是小写字母
 * Class LowLetter
 * @package Libs\Validate\Rule
 */
class LowLetter extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[a-z]+$/',$val);
    }
}