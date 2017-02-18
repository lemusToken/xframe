<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\Config\ConfigManager;
use \Libs\Console\Helper\Extend\HelperExtend;

class AppConfig extends HelperExtend{
    /**
     * 获取应用的名称
     * @return mixed
     */
    public function getAppName() {
        return ConfigManager::load('control')->item('appname');
    }

    public function getName() {
        return 'appConfig';
    }
}