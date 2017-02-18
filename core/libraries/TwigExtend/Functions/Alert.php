<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 16:17
 */

namespace Libs\TwigExtend\Functions;
use Libs\TwigExtend\Extend\ExtendFunction;


class Alert extends ExtendFunction{

    protected $name = 'alert';

    protected function fn() {
        return function(){
            $args = func_get_args();
            if (is_callable('dump')) {
                call_user_func_array('dump',$args);
            }
            else {
                call_user_func_array('var_dump',$args);
            }
        };
    }
}