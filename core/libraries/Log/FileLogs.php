<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/12
 * Time: 12:08
 */

namespace Libs\Log;

use Whoops\Exception\ErrorException;

class FileLogs extends Log{
    private $path='';
    private $base='';

    public function setBasePath($base) {
        $this->base = $base;
        if (!self::isWriteAble($this->base)) return false;
        return $this;
    }
    public function setLogger($path) {
        $this->path = $this->base.'/'.$path;
        if (!self::isWriteAble($this->path)) return false;
        return $this;
    }
    public function debug($message, array $content = [], $module = '') {
        $this->write('debug',$message,$content);
    }
    public function info($message, array $content = [], $module = '') {
        $this->write('info',$message,$content);
    }
    public function notice($message, array $content = [], $module = ''){
        $this->write('notice',$message,$content);
    }
    public function warning($message, array $content = [], $module = '') {
        $this->write('warning',$message,$content);
    }
    public function error($message, array $content = [], $module = '') {
        $this->write('error',$message,$content);
    }
    public function critical($message, array $content = [], $module = '') {
        $this->write('critical',$message,$content);
    }
    public function alert($message, array $content = [], $module = '') {
        $this->write('alert',$message,$content);
    }
    public function emergency($message, array $content = [], $module = '') {
        $this->write('emergency',$message,$content);
    }

    private function write($type,$message,array $content=[]) {
        if (!empty($content)) {
            $message = str_replace(array_keys($content),array_values($content),$message);
        }
        $pid = getmypid();
        $ms = self::msectime();
        $datetime = date('Y:m:d H:i:s');
        $message = $type.' | '.$pid.' | '.$ms.' | '.$datetime.' | '.$message."\n";
        file_put_contents($this->path.'/'.$type.'.'.date('YmdH').'.log', $message, FILE_APPEND|LOCK_EX);
    }

    /**
     * 路径不存在则新建，判断目录是否可写
     * @param $path
     * @return bool
     * @throws ErrorException
     */
    private static function isWriteAble($path) {
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        }
        if (!is_writeable($path)) {
            throw new ErrorException('文件目录:'.realpath($path)?:$path.'不可写');
        }
        return true;
    }

    /**
     * 毫秒数
     * @return float
     */
    private static function msectime() {
        list($tmp1, $tmp2) = explode(' ', microtime());
        return (float)sprintf('%.3f', (floatval($tmp1) + floatval($tmp2)));
    }
}