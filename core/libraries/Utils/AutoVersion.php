<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Utils;

/**
 * 自动版本号
 * Class AutoVersion
 * @package Libs\Utils
 */
class AutoVersion {

    private $file = [];

    /**
     * 文件控制文件
     * @param string $path
     * @return string
     */
    public function addFile($path) {
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
    public function value(){
        $file = $this->findExistFile();
        if (empty($file)) return false;
        $time = time();
        if (!file_exists($file)){
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
     * @return boolean
     */
    private function findExistFile() {
        foreach ($this->file as $v){
            if (file_exists($v)){
                return $v;
            }
        }
        return false;
    }
}