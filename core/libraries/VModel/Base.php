<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/24
 * Time: 10:07
 */

namespace Libs\VModel;
use Libs\Cache\Cache;
use Libs\Config\ConfigManager;
use Libs\DBAL\Db;
use Libs\ORM\Doctrine\EntityManager;
use Libs\Queue\Ques;
use Libs\Utils\Symbol\Symbol;


class Base {
    private static $instances=[];
    private $cacheKeyPre='';
    private $app='';

    public function __construct(){
        $this->init();
    }

    protected function init(){}

    /**
     * 生成数据实例
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public static function run($name) {
        $app = Ques::create('apps')->last();
        $name = ucfirst(ConfigManager::load('control')->item('appname')).'\\VM\\'.ucfirst($name);
        if (isset(self::$instances[$name])) return self::$instances[$name];

        if (!class_exists($name)) {
            throw new \ErrorException("The vmodel class '$name' does not exist!");
        }

        self::$instances[$name] = new $name;
        self::$instances[$name]->setApp($app);
        return self::$instances[$name];
    }

    public function setApp($app) {
        $this->app = $app;
    }

    /**
     * 用于调用应用切换后的数据类中的方法
     * 第一个参数表示方法名，其余均为参数
     * @return $this|mixed
     */
    public function act() {
        $args = func_get_args();
        if (empty($args)) {
            return $this;
        }
        $this->appChange($this->app);
        $name = array_shift($args);
        $result = call_user_func_array([$this,$name],$args);
        $this->appBack();
        return $result;
    }

    /**
     * 获取数据模型类
     * @param $entityName
     * @return mixed
     * @throws \Exception
     */
    protected function m($entityName) {
        $class = '\\Model\\'.$entityName;
        if (!class_exists($class)) {
            throw new \ErrorException("The model class '$class' does not exist!");
        }
        return new $class;
    }

    /**
     * 获取其他vm实例
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    protected function vm($name,$app='') {
        !empty($app)&&$this->appChange($app);
        $r = self::run($name);
        !empty($app)&&$this->appBack();
        return $r;
    }

    /**
     * 获取实体管理类
     * @return mixed
     */
    protected function em() {
        return EntityManager::get();
    }

    /**
     * 获取数据库连接实例
     * @param string $type config中的db.php中的配置，留空则为default
     * @param bool $renew 是否重新生成实例
     * @return \Doctrine\DBAL\Connection
     */
    protected function db($type='',$renew=false) {
        return Db::load($type)->connect($renew);
    }

    /**
     * 获取symbol实例
     * @param $cache
     * @return Symbol|null
     */
    protected function symbol($cache=null) {
        return Symbol::load($cache);
    }

    /**
     *
     * 获取配置管理实例
     * @param $type
     * @param string $engine
     * @return \Libs\Config\Config
     */
    protected function config($type,$engine='File') {
        return ConfigManager::load($type,$engine);
    }

    /**
     * 应用切换
     * @param $app
     */
    protected function appChange($app) {
        Ques::create('apps')->push($app);
        \BootStrap::run($app);
    }

    /**
     * 应用切换回当前应用
     */
    protected function appBack() {
        $q = Ques::create('apps');
        $q->pop();
        $last = $q->last();
        \BootStrap::run($last);
    }

    /**
     * 用于带有参数的缓存场景
     * 参数可以是数组或者字符串
     * @return Base
     */
    protected function flag(){
        $len = func_num_args();
        $args = func_get_args();
        if ($len>1){
            $this->cacheKeyPre = implode('-',func_get_args());
        }
        elseif ($len>0) {
            $this->cacheKeyPre = $args[0];
        }
        else {
            $this->cacheKeyPre = '';
        }
        return $this;
    }

    /**
     * $data不存在时表示获取缓存数据，存在时写入缓存
     * @param null $data
     * @param int $seconds
     * @return mixed|null
     */
    protected function cache ($data=null,$seconds=3600) {
        $caller = Cache::getPrevCaller();
        $key = $this->getCacheKey($caller['class'],$caller['method']);
        $cache = Cache::load('memcache','files');
        if (!isset($data)) {
            return $cache->get($key);
        }
        else {
            $cache->set($key,$data,$seconds);
        }
        return $data;
    }

    /**
     * 删除缓存
     * @param string $name 调用cache的方法名
     * @return mixed
     * @example $this->flag($sKey)->clear(__FUNCTION__);
     */
    protected function clear($name) {
        $caller = Cache::getPrevCaller();
        $key = $this->getCacheKey($caller['class'],$name);
        $cache = Cache::load('memcache','files');
        return $cache->delete($key);
    }

    protected function validate($tableName,$data,$options=[]) {
        return \Libs\Ruleset\Base::validateRuleSet($tableName,$data,$options);
    }

    /**
     * 获取缓存键
     * @param $class
     * @param $method
     * @return string
     */
    private function getCacheKey($class,$method) {
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$this->config('control')->item('appname');
        $key = "$host".($this->cacheKeyPre?'/'.$this->cacheKeyPre:'')."/{$class}/{$method}";
        $this->cacheKeyPre = '';
        return $key;
    }
}