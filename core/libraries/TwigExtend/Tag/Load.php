<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:46
 */

namespace Libs\TwigExtend\Tag;
use \Libs\TwigExtend\Extend\ExtendTag;
use \Libs\TwigExtend\Tag\TokenParser\Load as TokenLoad;

class Load extends ExtendTag {

    protected $name = 'load';

    protected function fn() {
        return new TokenLoad();
    }
}