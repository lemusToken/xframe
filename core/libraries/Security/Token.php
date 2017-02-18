<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/27
 * Time: 16:56
 */

namespace Libs\Security;

abstract class Token {
    protected static $secreteKey = '';
    protected $config = [];

    public static function setSecreteKey($key) {
        self::$secreteKey = $key;
    }

    public static function getSecreteKey() {
        return self::$secreteKey;
    }

    public static function load($type='exor',$config=[]) {
        switch ($type) {
            case 'md5':
                $s = new Token\Md5();
                $s->setConfig($config);
                break;
            default:
                $s = new Token\Exor;
                $s->setConfig($config);
                break;
        }
        return $s;
    }


    abstract public function create(array $payload);
    abstract public function verify($code);

    protected function setConfig($config) {
        $this->config = $config;
    }

    protected function getConfig() {
        return $this->config;
    }

    protected function getFinalKey() {
        $config = $this->getConfig();
        return empty($config['secretKey'])?self::getSecreteKey():$config['secretKey'];
    }
}