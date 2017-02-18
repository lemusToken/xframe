<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/21
 * Time: 14:39
 */

namespace Libs\TwigExtend\Extend;


abstract class ExtendFilter extends Extend {

    protected $version;
    protected $symbol;
    protected $extendType = 'filter';

    public function __construct() {
        parent::__construct();
        $this->version = parent::getHelper('version');
        $this->symbol = parent::getHelper('symbol');
    }

    protected function add() {
        $params = func_get_args();
        $this->twig->addFilter(new \Twig_SimpleFilter($params[0], $params[1]));
        return $this;
    }
}