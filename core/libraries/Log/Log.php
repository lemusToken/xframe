<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/12
 * Time: 12:08
 */

namespace Libs\Log;

abstract class Log {
    //设置日志文件根目录
    public abstract function setBasePath($base);
    //根目录下的分文件
    public abstract function setLogger($base);

    //调试
    public abstract function debug($message, array $content = [], $module = '');
    //普通
    public abstract function info($message, array $content = [], $module = '');
    //提示
    public abstract function notice($message, array $content = [], $module = '');
    //警告
    public abstract function warning($message, array $content = [], $module = '');
    //错误
    public abstract function error($message, array $content = [], $module = '');
    //严重
    public abstract function critical($message, array $content = [], $module = '');
    //打印
    public abstract function alert($message, array $content = [], $module = '');
    //紧急
    public abstract function emergency($message, array $content = [], $module = '');
}