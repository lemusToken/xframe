<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\Console\Helper\Extend\HelperExtend;
use \Libs\Statics\Pack as StaticPack;
use \Libs\Statics\Find as StaticFind;
use \Libs\Utils\Symbol\Symbol as Sym;

class Pack extends HelperExtend{
    /**
     * 合并以及压缩
     * @return array
     */
    public function minify($str,$url='') {
        return StaticPack::minify($str,$url);
    }

    /**
     * 生成静态文件
     * @param $str
     * @param string $url
     * @param string $base
     * @param array $params
     * @return array
     */
    public function create($str,$url='',$base='',$params=[]) {
        $data = is_array($str)?$str:$this->minify($str,$url);
        return StaticPack::create($data,$base,$params,Sym::load());
    }

    /**
     * 清除页面中所有的静态资源
     * @param $str
     * @return mixed
     */
    public function cleanAll($str) {
        return StaticFind::cleanAll($str);
    }

    /**
     * 获取页面中标记ignore的资源
     * @param $str
     * @return mixed
     */
    public function getIgnore($str) {
        return StaticFind::findIgnore($str);
    }

    /**
     * 获取外部资源的地址
     * @param $str
     * @return string
     */
    public function getUrl($str,$host) {
        $url = StaticFind::findUrl($str,true);
        return $this->formatResource(Sym::load()->parse($url),$host);
    }

    public function formatResource($str,$host) {
        return StaticFind::resourcePath($str,$host);
    }

    /**
     * 页面代码中添加js代码
     * @param $str
     * @param $js
     * @return mixed|string
     */
    public function addJS($str,$js) {
        return StaticPack::addJS($str,$js);
    }

    /**
     * 页面代码中添加css代码
     * @param $str
     * @param $css
     * @return mixed|string
     */
    public function addCSS($str,$css) {
        return StaticPack::addCSS($str,$css);
    }

    public function getName() {
        return 'pack';
    }
}