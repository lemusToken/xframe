<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\Console\Helper\Extend\HelperExtend;
use \Libs\Utils\Common as CommonFn;

class Common extends HelperExtend{
    /**
     * utf8转gbk
     * @return mixed
     */
    public function chs() {
        $os = CommonFn::checkOS();
        //如果是windows系统，转gbk
        if ($os==='win') {
            return call_user_func_array('\Libs\Utils\Common::chs',func_get_args());
        }
        return call_user_func_array('\Libs\Utils\Common::combineStr',func_get_args());
    }

    public function scandirDeep($path,$data) {
        return CommonFn::scandirDeep($path,$data);
    }

    public function getName() {
        return 'common';
    }
}