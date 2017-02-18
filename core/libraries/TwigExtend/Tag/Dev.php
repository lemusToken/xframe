<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:46
 */

namespace Libs\TwigExtend\Tag;
use \Libs\TwigExtend\Extend\ExtendTag;
use \Libs\TwigExtend\Tag\TokenParser\Dev as TokenDev;

class Dev extends ExtendTag {

    protected $name = 'dev';

    protected function fn() {
        return new TokenDev();
    }
}