<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Utils\Symbol;

/**
 * 文件缓存符号
 * Class SymbolCache
 * @package Libs\Utils\Symbol
 */
class SymbolCache {
    private $namespace;
    private $cache;

    public function __construct(\phpFastCache\Drivers\xcache $cache) {
        $this->namespace = '/file/cache/symbol';
        $this->cache = $cache;
    }

    public function save($val) {
        $this->cache->set($this->namespace,$val);
    }

    public function get() {
        return $this->cache->get($this->namespace);
    }

    public function getFiles() {
        return $this->cache->get($this->namespace.'/flist');
    }

    /**
     * 检测数据文件是否有更新
     * @param $file
     * @return bool
     */
    public function checkFile($file) {
        $str =  '';
        $namespace = $this->namespace;
        $check = $this->cache->get($namespace.'/ftime');
        foreach ($file as $v) {
            //filemtime 高并发时的性能瓶颈
            $str .= filemtime($v);
        }
        if ($check!==$str){
            $this->cache->set($namespace.'/ftime',$str);
            $this->cache->set($namespace.'/flist',$file);
            return true;
        }
        return false;
    }

    /**
     * 检测数据是否有更新
     * @param $src
     * @param $dst
     * @return bool
     */
    public function checkDiff($src,$dst) {
        $result = false;
        foreach ($dst as $k=>$v) {
            if (!isset($src[$k])||$src[$k]!==$v) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}