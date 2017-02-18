<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\Router\Url as RouterUrl;
use \Libs\Console\Helper\Extend\HelperExtend;

class Url extends HelperExtend{
    /**
     * 直接执行路由
     * @param $uri
     */
    public function router($uri) {
        \BootStrap::run()->control($uri);
    }

    /**
     * 通过地址解析路由
     * @param $uri
     * @return array
     */
    public function runRouter($uri) {
        return \BootStrap::run()->runRouter($uri);
    }

    /**
     * 获取模板信息
     * @param $app
     * @param $act
     * @return bool
     */
    public function getView($app,$act) {
        return \Libs\Application\Base::run($app,$act,false,false,true);
    }

    /**
     * 分析url
     * @param $url
     * @return array
     */
    public function parse($url) {
        return RouterUrl::parse($url);
    }

    /**
     * curl访问地址
     * @param $url
     * @param string $type 访问方式
     * @param bool $print 是否打印返回结果
     * @return bool|string
     */
    public function curl($url,$type='get',$print=false) {
        $response = \Requests::$type($url);
        if ($print)
            echo $response->body;
        else {
            return $response->body;
        }
        return true;
    }

    public function getName() {
        return 'url';
    }
}