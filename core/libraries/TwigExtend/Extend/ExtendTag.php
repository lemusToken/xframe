<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:39
 */

namespace Libs\TwigExtend\Extend;


abstract class ExtendTag extends Extend {

    public function __construct() {
        parent::__construct();
    }

    public function add() {
        $params = func_get_args();
        $token = $params[0];
        if (is_object($params[1])) {
            $token = $params[1];
        }
        $this->twig->addTokenParser($token);
        return $this;
    }
}