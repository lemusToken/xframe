<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 16:17
 */

namespace Libs\TwigExtend\Functions;
use Libs\TwigExtend\Extend\ExtendFunction;
use \Libs\Application\Base as AppBase;
use \Libs\Router\Run as RouterRun;
use \Libs\Utils\Common;
use \Libs\Utils\Request;


class _LoadData extends ExtendFunction{

    protected $name = '_loadData';
    //临时缓存
    private static $cacheTemp=[];

    protected function fn() {
        $fn = self::getHelper('function/getAppActByRouter');
        return function($path) use ($fn){
            if (!empty(self::$cacheTemp['template-data'][$path])) {
                return self::$cacheTemp['template-data'][$path];
            }

            $data = $fn($path);
            $app = $data['app'];
            $act = $data['act'];

            $fn = null;
            if (empty($app)) {
                return [];
            }
            return self::$cacheTemp['template-data'][$path] = AppBase::run($app,$act,false,true);
        };
    }
}