<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 16:17
 */

namespace Libs\TwigExtend\Functions;
use Libs\TwigExtend\Extend\ExtendFunction;


class Typeof extends ExtendFunction{

    protected $name = 'typeof';

    protected function fn() {
        return function($val){
            if (is_array($val)) return 'Array';
            if (is_string($val)) return 'String';
            if (is_callable($val)) return 'Function';
            if (is_int($val)) return 'Integer';
            if (is_float($val)) return 'Float';
            if (is_bool($val)) return 'Boolean';
            if (is_nan($val)) return 'Nan';
            if (is_null($val)) return 'Null';
            return '';
        };
    }
}