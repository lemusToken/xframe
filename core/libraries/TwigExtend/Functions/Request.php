<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 16:17
 */

namespace Libs\TwigExtend\Functions;
use Libs\TwigExtend\Extend\ExtendFunction;
use Libs\Utils\Request as LibRequest;


class Request extends ExtendFunction{

    protected $name = 'request';

    protected function fn() {
        return function($type,$k){
            return LibRequest::$type($k);
        };
    }
}