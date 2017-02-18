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


class Load extends ExtendFunction{

    protected $name = 'load';
    //临时缓存
    private static $cacheTemp=[];

    protected function fn() {
        return function($path){
            if (!empty(self::$cacheTemp['template'][$path])) {
                return self::$cacheTemp['template'][$path];
            }

            $data = $this->getAppActByRouter($path);
            $app = $data['app'];
            $act = $data['act'];

            if (empty($app)) {
                return '';
            }
            return self::$cacheTemp['template'][$path] = AppBase::run($app,$act,true);
        };
    }

    protected function registerHelper() {
        return [
            'getAppActByRouter'=>function($path) {
                return $this->getAppActByRouter($path);
            }
        ];
    }

    private function getAppActByRouter($path) {
        //解析路由
        $dataRouter = RouterRun::load()->get($path);
        $app = $dataRouter->get('app');
        $act = $dataRouter->get('act');

        if (empty($app)) {
            return ['app'=>'','act'=>''];
        }
        $params = $dataRouter->get();

        //直接将数据写入$request、post、get中
        if (is_array($params)) {
            $isPost = Common::isPost();
            $isGet = Common::isGet();
            foreach ($params as $k=>$v) {
                Request::request($k,$v);
                if ($isPost) {
                    Request::post($k,$v);
                }
                elseif ($isGet) {
                    Request::get($k,$v);
                }
            }
        }
        return ['app'=>$app,'act'=>$act];
    }
}