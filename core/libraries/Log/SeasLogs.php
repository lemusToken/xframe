<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/12
 * Time: 12:08
 */

namespace Libs\Log;


class SeasLogs extends Log {
    public function setBasePath($base) {
        \SeasLog::setBasePath($base);
        return $this;
    }
    public function setLogger($path) {
        \SeasLog::setLogger($path);
        return $this;
    }
    public function debug($message, array $content = [], $module = '') {
        \SeasLog::debug($this->write($message),$content,$module);
    }
    public function info($message, array $content = [], $module = '') {
        \SeasLog::info($this->write($message),$content,$module);
    }
    public function notice($message, array $content = [], $module = ''){
        \SeasLog::notice($this->write($message),$content,$module);
    }
    public function warning($message, array $content = [], $module = '') {
        \SeasLog::warning($this->write($message),$content,$module);
    }
    public function error($message, array $content = [], $module = '') {
        \SeasLog::error($this->write($message),$content,$module);
    }
    public function critical($message, array $content = [], $module = '') {
        \SeasLog::critical($this->write($message),$content,$module);
    }
    public function alert($message, array $content = [], $module = '') {
        \SeasLog::alert($this->write($message),$content,$module);
    }
    public function emergency($message, array $content = [], $module = '') {
        \SeasLog::emergency($this->write($message),$content,$module);
    }
    public function write($message){
        return $message;
    }
}