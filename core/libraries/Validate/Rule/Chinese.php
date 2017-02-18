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
 * 判断是中文
 * Class Chinese
 * @package Libs\Validate\Rule
 */
class Chinese extends Validate{

    protected function rule($val,$params=[]) {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$val);
    }
}