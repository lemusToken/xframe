<?php

namespace Libs\Router;

/**
 * url解析类
 * Class Url
 * @package Router
 * @author xule
 */
class Url{

    /**
     * 解析链接中的host(查询域名)、uri、path(查询路径)、query(查询)、params(查询参数)
     * @param string $url
     * @return array
     */
    public static function parse($url=''){
        $result = [
            'host'=>'',
            'uri'=>'',
            'path'=>'',
            'query'=>'',
            'params'=>[]
        ];

        $result['uri'] = $uri = empty($url)?$_SERVER['REQUEST_URI']:$url;
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        //认为//之后的第一个/所在位置之前为host
        $p = strpos($uri,'//');
        $l = false;
        if (empty($host)&&$p!==false) {
            $l = strpos($uri,'/',$p+2);
            $host = $l===false?$uri:substr($uri,0,$l);
        }

        $result['host'] = $host;
        $result['path'] = $l!==false?$uri = substr($uri,$l+1):'';

        $p = strpos($uri,'?');
        if ($p!==false) {
            $result['path'] = substr($uri,0,$p);
            $result['query'] = substr($uri,$p);
            $result['params'] = self::params($result['query']);
        }
        return $result;
    }

    /**
     * 解析url中的参数
     * @param $q
     * @return array
     */
    public static function params($q){
        $params = [];
        if (empty($q)) return $params;
        strpos($q,'?')===false&&($q='?'.$q);
        if (($p=strpos($q,'?'))!==false&&$p+1<strlen($q)){
            $query = substr($q,$p+1);
            if (strpos($query,'&')!==false){
                $plist = explode('&',$query);
                foreach ($plist as $k=>$v){
                    $_p = strpos($v,'=');
                    if ($_p===false) continue;
                    $params[substr($v,0,$_p)] = substr($v,$_p+1);
                }
            }
            else{
                $_p = strpos($query,'=');
                $params[substr($query,0,$_p)] = substr($query,$_p+1);
            }
        }
        return $params;
    }
}