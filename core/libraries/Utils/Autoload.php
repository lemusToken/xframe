<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/14
 * Time: 14:17
 */

namespace Libs\Utils;

use Composer\Autoload\ClassLoader;

/**
 * 基于composer的自动加载类(代理)
 * Class Autoload
 * @package Libs\Utils
 */
class Autoload {

    private static $instance;
    private $loader;
    private $registered=false;

    private function __construct() {
        $this->loader = new ClassLoader;
    }

    public function psr4($map) {
        if (!empty($map)) {
            foreach ($map as $namespace => $path) {
                $this->loader->setPsr4($namespace, $path);
            };
            $this->register();
        }
        return $this;
    }

    public function psr0($map) {
        if (!empty($map)) {
            foreach ($map as $namespace => $path) {
                $this->loader->set($namespace, $path);
            }
            $this->register();
        }
        return $this;
    }

    public function files($maps) {
        if (is_array($maps)) {
            foreach ($maps as $v) {
                include_once $v;
            }
        }
        else{
            include_once $maps;
        }
        return $this;
    }

    private function register() {
        if ($this->registered) return true;
        $this->loader->register(true);
        $this->registered = true;
        return true;
    }

    public static function create() {
        return self::$instance?:self::$instance = new self;
    }
}