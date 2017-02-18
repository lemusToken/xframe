<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/12
 * Time: 12:08
 */

namespace Libs\Log;

/**
 * 默认使用seaslogs，如果seaslogs不存在则使用写文件
 * Class Logs
 * @package Libs\Log
 */
class Logs{
    private static $instance=[];
    private static $eng='file';

    public static function load() {
        //启用Seaslog
        if (self::$eng==='seaslog'&&class_exists('SeasLog')){
            $i = self::$instance['SeasLogs'] = empty(self::$instance['SeasLogs'])?new SeasLogs:self::$instance['SeasLogs'];
        }
        else{
            $i = self::$instance['FileLogs'] = empty(self::$instance['FileLogs'])?new FileLogs:self::$instance['FileLogs'];
        }
        return $i;
    }

    public static function setEngine($eng) {
        self::$eng = $eng;
    }

    /**
     * 定义捕捉错误函数
     */
    public static function addErrorReporting() {
        restore_exception_handler();
        restore_error_handler();
        set_error_handler(function($level, $message, $file, $line){
            $args = func_get_args();
            self::load()->setLogger('error')->error(implode(' ',[$args[1],$args[2],$args[3]]));
        });
        set_exception_handler(function(\Exception $e){
            self::load()->setLogger('error')->error(implode(' ',[$e->getMessage(),$e->getFile(),$e->getLine()]));
        });
        register_shutdown_function(function(){
            $e = error_get_last();
            if (empty($e)) return false;
            self::load()->setLogger('error')->error(implode(' ',[$e['message'],$e['file'],$e['line']]));
            return true;
        });
    }
}