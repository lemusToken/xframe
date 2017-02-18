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
 * 判断是字母
 * Class Letter
 * @package Libs\Validate\Rule
 */
class Letter extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[a-zA-Z]+$/',$val);
    }
}