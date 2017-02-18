<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/31
 * Time: 10:59
 */

namespace Libs\ORM\Doctrine;
use Doctrine\ORM\Tools\Setup;
use Libs\Config\ConfigManager;
use Libs\DBAL\Db;
use Libs\Utils\Common;
use Libs\Utils\Symbol\Symbol;


class EntityManager {
    private static $instance;
    private $em;

    private function __construct() {
        $this->_em();
    }

    /**
     * 获取数据实体管理类
     * @return mixed
     */
    public static function get() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance->em;
    }

    private function _em() {
        //判断是否是生产环境
        $prov = Common::isProv();
        //获取配置
        $config = ConfigManager::load('doctrine')->item(true,true);

        $cache = null;
        //如果是生产环境，设置缓存
        if ($prov) {
            $engine = '\Doctrine\Common\Cache\\'.$config['cache'].'Cache';
            $cache = new $engine;
        }

        $proxyPath = $config['proxies'];
        $configORM = Setup::createConfiguration(!$prov,$proxyPath,$cache);

        $metaDataPath = Symbol::load()->parse(':bootstrap/Maps',true);
        $yamlDriver = new \Doctrine\ORM\Mapping\Driver\YamlDriver($metaDataPath,'.yml');
        $configORM->setMetadataDriverImpl($yamlDriver);

        return $this->em = \Doctrine\ORM\EntityManager::create(Db::load()->connect(), $configORM);
    }
}