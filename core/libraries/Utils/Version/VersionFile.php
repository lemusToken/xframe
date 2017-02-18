<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/12
 * Time: 23:04
 */

namespace Libs\Utils\Version;

/**
 * 资源版本号(文件控制)
 * Class VersionFile
 * @package Libs\Config
 */
class VersionFile extends Version{
    private $file = [];

    /**
     * 添加控制文件路径
     * @param string $path 路径
     */
    public function add($path) {
        if (strpos($path,'|')!==false){
            $this->file = explode('|',$path);
        }
        else{
            $this->file[] = $path;
        }
    }

    /**
     * 获取版本号
     * @return bool|int
     */
    public function value() {
        $file = $this->find();
        if (empty($file)&&isset($this->file[0])) $file = $this->file[0];
        $time = time();
        if (!file_exists($file)){
            if (!file_exists($p = dirname($file))) {
                mkdir($p,0777,true);
            }
            file_put_contents($file,'');
        }
        else{
            //修改时间
            $time = filemtime($file);
        }
        return $time;
    }

    /**
     * 取得存在的控制文件
     * @return bool
     */
    protected function find() {
        foreach ($this->file as $v){
            if (file_exists($v)){
                return $v;
            }
        }
        return false;
    }
}