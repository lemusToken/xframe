<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:39
 */

namespace Libs\TwigExtend\Extend;


abstract class ExtendFunction extends Extend {

    protected $extendType = 'function';

    public function __construct() {
        parent::__construct();
    }

    public function add() {
        $params = func_get_args();
        $this->twig->addFunction(new \Twig_SimpleFunction($params[0], $params[1]));
        return $this;
    }
}