<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\Utils\Symbol\Symbol as Sym;
use \Libs\Console\Helper\Extend\HelperExtend;

class Symbol extends HelperExtend{
    /**
     * 获取symbol实例
     * @return mixed
     */
    public function load() {
        return Sym::load();
    }

    public function getName() {
        return 'symbol';
    }
}