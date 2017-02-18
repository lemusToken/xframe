<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/19
 * Time: 20:17
 */

namespace Libs\Router;


class Run {
    private $path;
    private $cache;
    private $cacheKey;
    private static $cacheData=[];
    private static $instance=null;

    /**
     * Run constructor.
     */
    private function __construct() {
    }

    /**
     * 获得实例
     * @return Run
     */
    public static function load($path=null,$cache=null) {
        if (empty(self::$instance)) {
            self::$instance=new self;
            $path&&self::$instance->setPath($path);
            $cache&&self::$instance->setCache($cache);
            self::$instance->add();
        }
        return self::$instance;
    }

    /**
     * 设置路由文件的根目录
     * @param $path string $path 路由文件所在的根目录
     */
    public function setPath($path) {
        $this->path = $path;
        $this->cacheKey = 'router/'.$this->path;
    }

    /**
     * 设置使用的缓存类
     * @param $cache
     */
    public function setCache($cache) {
        $this->cache = $cache;
    }

    /**
     * 获取路由解析结果
     * @param $uri
     * @return Data
     */
    public function get($uri) {
        return Router::run($uri);
    }

    /**
     * 添加路由文件内容到路由中
     */
    private function add() {
        $ptime = filemtime($this->path);
        $cache = $this->cache->get($this->cacheKey);
        if ($cache&&$cache['_']===$ptime) {
            $this->addArray($cache);
            return true;
        }

        $fileList = $this->scan();

        if ($fileList){
            //router.php始终在最前
            if (in_array('router.php',$fileList)){
                self::$cacheData['router.php'] = $this->addFile('router.php');
            }

            foreach ($fileList as $k => $v) {
                if ($v === '.' || $v === '..' || $v==='router.php') continue;
                self::$cacheData[$v] = $this->addFile($v);
            }

            self::$cacheData['_'] = $ptime;
            $this->cache->set($this->cacheKey,self::$cacheData);
            self::$cacheData = [];
        }
        return true;
    }

    private function addArray($ary) {
        if (!empty($ary['router.php'])) {
            $v = $ary['router.php'];
            foreach ($v as $kk => $vv) {
                if (strpos($kk, '/') !== 0) {
                    $kk = '/' . $kk;;
                }
                $vv[1] = isset($vv[1])?$vv[1]:'';
                Router::add($kk, $vv[0], $vv[1]);
            }
            unset($ary['router.php']);
        }
        foreach ($ary as $k=>$v) {
            if ($k==='_') continue;
            $_p = strpos($k, 'router.php');
            $_n = '';
            if ($_p > 0) {
                $_n = substr($k, 0, $_p - 1);
                if (strpos($_n, '.') !== false) {
                    $_n = str_replace('.', '/', $_n);
                }
            }
            foreach ($v as $kk => $vv) {
                if (strpos($kk, '/') === 0) {
                    $kk = $_n . $kk;
                } else {
                    $kk = $_n . '/' . $kk;
                }
                $vv[1] = isset($vv[1])?$vv[1]:'';
                Router::add($kk, $vv[0], $vv[1]);
            }
        }
    }

    /**
     * 从文件中添加路由
     * @param $file
     * @return array
     */
    private function addFile($file) {
        $router = include_once $this->path.  '/' . $file;
        is_array($router)&&$this->addArray([$file=>$router]);
        return $router;
    }

    /**
     * 扫描路由目录下的所有路由文件
     * @return array|bool
     */
    private function scan() {
        if (!file_exists($this->path)) return false;
        return scandir($this->path);
    }
}