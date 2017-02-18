<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/12
 * Time: 23:04
 */

namespace Libs\Utils\Version;

/**
 * 资源版本号接口
 * Class Version
 * @package Libs\Utils\Version
 */
abstract class Version {
    abstract public function add($path);
    abstract protected function find();
    abstract public function value();
}