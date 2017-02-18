<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:39
 */

namespace Libs\TwigExtend\Extend;


class ExtendGlobal extends Extend {

    public function __construct() {
        parent::__construct();
    }

    public function add() {
        $params = func_get_args();
        $this->twig->addGlobal($params[0], $params[1]);
        return $this;
    }

    public function fn() {
        return false;
    }
}