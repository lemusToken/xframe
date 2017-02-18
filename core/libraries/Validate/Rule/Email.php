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
 * 判断是邮箱
 * Class Email
 * @package Libs\Validate\Rule
 */
class Email extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[\w\._]+@\w+(?:\.\w+){1,2}$/',$val);
    }
}