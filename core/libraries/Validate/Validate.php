<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/1
 * Time: 17:07
 */

namespace Libs\Validate;


abstract class Validate {

    protected $name = '';

    /**
     * @param string $val 值
     * @param array $params 控制参数
     * @return mixed
     */
    abstract protected function rule($val,$params=[]);

    public function run($val,$params=[]) {
        return $this->rule($val,$params);
    }
}