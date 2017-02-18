<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/16
 * Time: 13:45
 */

namespace Libs\Cache;
use \phpFastCache\CacheManager;
use \phpFastCache\Core\DriverAbstract;
use \phpFastCache\Core\DriverInterface;

class Cache {

    private static $instance=[];
    private static $config;
    private $cache;

    private function __construct($type) {}

    public static function setConfig($config) {
        self::$config = $config;
    }

    /**
     * 获取缓存引擎
     * 多个缓存引擎参数表示依此选取第一个有效的引擎
     * @param string $type apc,files,cookie,memcache,memcached,predis,redis,sqlite,ssdb,wincache,xcache
     * @return DriverAbstract|false
     * @throws \ErrorException
     */
    public static function load($type='files') {
        $args = func_get_args();
        foreach ($args as $val) {
            if (empty(self::$instance[$val])) {
                self::$config['storage'] = $val;
                CacheManager::setup(self::$config);
                $inst = CacheManager::getInstance();
                if (self::checkDriver($inst)) {
                    self::$instance[$val]=new self($val);
                    self::$instance[$val]->cache = $inst;
                    $type = $val;
                    break;
                }
            }
            elseif (self::checkDriver(self::$instance[$val]->cache)) {
                $type = $val;
                break;
            }
        }
        if (!isset(self::$instance[$type])) {
            return false;
//            throw new \ErrorException($type.' 缓存引擎初始化失败！');
        }
        return self::$instance[$type]->cache;
    }

    /**
     * 获取前面调用的类和方法，用于自动生成缓存键
     * @param int $num
     * @return array
     */
    public static function getPrevCaller($num=1) {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,$num+2);
        $trace = ob_get_contents();
        ob_end_clean();
        $start = strpos($trace,'#'.($num+1));
        $end = strpos($trace,'called',$start);

        if (!$end) {
            $content = substr($trace,$start+strlen('#'.($num+1))+2);
        }
        else {
            $content = trim(str_replace('#'.($num+1),'',substr($trace,$start,$end-$start)));
        }
        $content = explode('->',$content);
        $content[1] = trim(str_replace('()','',$content[1]));
        return [
            'class'=>$content[0],
            'method'=>$content[1]
        ];
    }

    /**
     * 检测服务是否可用
     * @param DriverInterface $inst
     * @return bool
     */
    public static function checkDriver($inst) {
        if ($inst instanceof \phpFastCache\Drivers\files) {
            return $inst->checkdriver();
        }
        $check = false;
        try {
            if ($inst->checkdriver()) {
                $status = $inst->driver_stats();
                if (!empty($status['data'])) {
                    $check = true;
                }
            }
        }
        catch (\ErrorException $e) {
            $check = false;
        }
        return $check;
    }
}