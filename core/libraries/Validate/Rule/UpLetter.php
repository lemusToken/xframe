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
 * 判断是大写字母
 * Class UpLetter
 * @package Libs\Validate\Rule
 */
class UpLetter extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[A-Z]+$/',$val);
    }
}