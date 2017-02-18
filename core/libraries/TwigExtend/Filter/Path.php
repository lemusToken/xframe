<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:46
 */

namespace Libs\TwigExtend\Filter;
use Libs\TwigExtend\Extend\ExtendFilter;

class Path extends ExtendFilter {

    protected $name = 'path';

    protected function fn() {
        return function($path,$autoVersion=false){
            return $this->path($path,$autoVersion);
        };
    }

    protected function registerHelper() {
        return [
            'path'=>function($path,$autoVersion=false) {
                return $this->path($path,$autoVersion);
            }
        ];
    }

    /**
     * 路径解析，支持符号
     * @param string $path 路径
     * @param bool $autoVersion 自动版本号
     * @return string
     */
    private function path($path,$autoVersion=false) {
        if ($autoVersion){
            $version = $this->version->value();
            if (strpos($path,'?')!==false){
                $path .= "&v=$version";
            }
            else if (($p=strpos($path,'#'))!==false){
                $j = substr($path,$p+1);
                $path = str_replace("#$j","?v=$version#{$j}",$path);
            }
            else{
                $path .= "?v=$version";
            }
        }
        return $this->symbol->parse($path);
    }
}