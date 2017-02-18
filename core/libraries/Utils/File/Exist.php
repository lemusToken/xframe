<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Utils\File;

/**
 * 文件是否存在
 * Class Exist
 * @package Libs\Utils\File
 */
class Exist {

    /**
     * 本地文件是否存在
     * @param $path
     * @return boolean
     */
    public static function local($path){
        return file_exists($path);
    }

    /**
     * 判断远程文件是否存在
     * @param $url
     * @return boolean
     */
    public static function remote($url){
        if(ini_get('allow_url_fopen')){
            if(@fopen($url,'r')) return true;
        }
        else{
            $info=parse_url($url);
            $host=$info['host'];
            $path=$info['path'];
            $fp=fsockopen($host,80, $errno, $errstr, 10);
            if(!$fp) return false;
            fputs($fp,"GET {$path} HTTP/1.1 \r\nhost:{$host}\r\n\r\n");
            if(preg_match('/HTTP\/1.1 200/',fgets($fp,1024))) return true;
        }
        return false;
    }

    /**
     * 判断本地或者远程文件是否存在
     * @param $path
     * @return bool
     */
    public static function check($path){
        if (strpos($path,'http://')!==false||strpos($path,'https://')!==false){
            return self::remote($path);
        }
        return self::local($path);
    }
}