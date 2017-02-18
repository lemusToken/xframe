<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:46
 */

namespace Libs\TwigExtend\Filter;
use Libs\Application\Base;
use Libs\TwigExtend\Extend\ExtendFilter;


class StaticLine extends ExtendFilter {

    protected $name = 'static';

    protected function fn() {
        return function($string,$attr=[]){
            return $this->externalStatic($string,$attr);
        };
    }

    /**
     * 解析外部资源
     * @param $string
     * @return string
     */
    public function externalStatic($string) {
        $pathHelper = self::getHelper('filter/path');
        $args = func_get_args();
        $string = $pathHelper($string,true);
        $path = $string;
        if (strpos($string,'#')!==false||strpos($string,'?')!==false){
            $path = preg_replace('/[#\?].*?$/','',$string);
        }
        if (Base::isExistAsset($path)) {
            return '';
        }
        Base::saveExistAsset($path);

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $result = '';
        $attribute = [];
        if (!empty($args[1])&&is_array($args[1])){
            foreach ($args[1] as $k=>$v){
                $attribute[] = "$k='$v'";
            }
        }
        $attribute = implode(' ',$attribute);
        switch($ext){
            case 'js':
                $result = "<script $attribute src='$string'></script>";
                break;
            case 'css':
                $result = "<link $attribute rel='stylesheet' href='$string'>";
                break;
        }
        $pathHelper = null;
        return $result;
    }
}