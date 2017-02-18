<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:46
 */

namespace Libs\TwigExtend\Tag;
use \Libs\TwigExtend\Extend\ExtendTag;
use \Libs\TwigExtend\Tag\TokenParser\Pro as TokenPro;

class Pro extends ExtendTag {

    protected $name = 'pro';

    protected function fn() {
        return new TokenPro();
    }
}