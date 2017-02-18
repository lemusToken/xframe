<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/30
 * Time: 12:30
 */

namespace Libs\Queue;


class Ques {

    private static $instance=[];
    private static $q=[];

    private function __construct() {}

    public static function create($name) {
        return isset(self::$instance[$name])?self::$instance[$name]:self::$instance[$name] = new self;
    }

    public function push($item) {
        self::$q[] = $item;
    }

    public function pop() {
        array_pop(self::$q);
    }

    public function last() {
        return end(self::$q);
    }

    public function all() {
        return self::$q;
    }
}