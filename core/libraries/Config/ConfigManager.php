<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/11
 * Time: 09:30
 */

namespace Libs\Config;
use \Libs\Config\File\ConfigFile;
use \Libs\Utils\Symbol\Symbol;

/**
 * 配置管理
 * Class ConfigManager
 * @package Libs\Utils
 */
class ConfigManager {
    //不同应用的配置
    public static $app='';

    /**
     * 配置引擎
     * @param string $name
     * @param string $engine
     * @return Config
     */
    public static function load($name,$engine='File') {
        $config = null;
        switch ($engine) {
            case 'File':
                $params['app'] = self::$app;
                $config = ConfigFile::singleton($name,$params,Symbol::load());
                break;
        }
        return $config;
    }

    /**
     * @param string $name 判断配置文件是否存在
     * @return bool
     */
    public static function exist($name) {
        return self::load($name)&&self::load($name)->isExist($name);
    }
}