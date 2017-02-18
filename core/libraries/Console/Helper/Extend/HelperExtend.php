<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 11:23
 */

namespace Libs\Console\Helper\Extend;
use Symfony\Component\Console\Helper\Helper;


abstract class HelperExtend extends Helper{

    /**
     * helper名称
     * @return mixed
     */
    abstract function getName();
}