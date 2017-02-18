<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/15
 * Time: 17:34
 */

namespace Libs\Utils;


class Url {

    public static function create($url,$params,$char='/') {
        if (empty($params)) return $url;
        $ps = '';
        if (!empty($params['_'])) {
            $ps = implode($char,$params['_']);
            unset($params['_']);
        }
        if (!empty($params)) {
            !empty($ps)&&$ps .= $char;
            foreach ($params as $k=>$v) {
                if ($char==='&') {
                    $ps .= "{$k}={$v}&";
                }
                else{
                    $ps .= "{$k}{$char}{$v}{$char}";
                }
            }
        }
        if (strrpos($ps,$char)===strlen($ps)-strlen($char)) {
            $ps = substr($ps,0,strlen($ps)-strlen($char));
        }
        if (strpos($url,'?')!==false) {
            $url .= '&';
        }
        elseif ($char==='&') {
            $url .= '?';
        }
        elseif (strpos($ps,$char)!==0) {
            $url .= $char;
        }
        $url .= $ps;
        return $url;
    }
}