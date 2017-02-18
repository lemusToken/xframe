<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/19
 * Time: 14:46
 */

use \Libs\Application\Base as AppBase;
use \Libs\Cache\Cache;
use \Libs\Config\ConfigManager;
use \Libs\PartitionTable\Create as PartitionTableCreate;
use \Libs\Console\Console;
use \Libs\DBAL\Db;
use \Libs\Log\Logs;
use \Libs\Queue\Ques;
use \Libs\Router\Run as RouterRun;
use \Libs\Session\SessionManager;
use \Libs\TwigExtend\ExtendAll;
use \Libs\Upload\Upload;
use \Libs\Security\Token;
use \Libs\Utils\Autoload;
use \Libs\Utils\Catalog;
use \Libs\Utils\Common;
use \Libs\Utils\Request;
use \Libs\Utils\Symbol\Symbol;
use \Libs\Utils\Version\VersionFile;
use \Libs\Fapis\Apis;
use \Whoops\Handler;
use \Whoops\Run;

class BootStrap {

    private static $instance=[];
    private $isCli=false;

    /**
     * BootStrap constructor.
     */
    protected function __construct() {
        //是否是cli模式
        $this->isCli = Common::isCli();
    }

    /**
     * 单例
     * @param string $app 应用名称
     * @return BootStrap|null
     */
    public static function run ($app='') {
        $app = empty($app)?'_':$app;
        if (!isset(self::$instance[$app])||!self::$instance[$app] instanceof self) {
            self::$instance[$app] = new self();
            if ($app==='_') {
                self::$instance[$app]->init();
            }
            else {
                self::$instance[$app]->initApp($app);
            }
        }
        elseif ($app==='_') {
            self::restoreApp();
        }
        else {
            self::$instance[$app]->initApp($app);
        }
        return self::$instance[$app];
    }

    /**
     * 执行控制器指定动作
     * @param string $uri 输入的uri
     * @return bool
     */
    public function control($uri='') {
        if ($this->isCli&&empty($uri)) return false;
        $data = $this->router($uri);
        //运行控制器+动作
        AppBase::run(ucfirst($data['app']),$data['act']);
        return true;
    }

    public function runRouter($uri='') {
        return $this->router($uri);
    }

    /**
     * 初始化，功能组合
     */
    private function init () {
        //建立符号表
        $this->symbol();
        Ques::create('apps')->push('_');
        //设置时区
        $this->timezone();
        //错误控制
        $this->debugModel()&&$this->errorTip();
        //日志功能
        $this->log();
        //安全
        $this->security();
        //配置缓存系统
        $this->cache();
        //配置路由
        $this->setRouter();
        //配置fapis客户端
        $this->apis();
        //配置数据库
        $this->db();
        //规则集配置
        $this->ruleset();
        //session共享
        $this->session();
        //配置模板
        $this->twig();
        //模型自动加载
        $this->autoload();
        //分表支持
        $this->partition();
        //阿里云oss
        $this->upload();
        //控制器
        $this->control();
        //cli模式
        $this->cliRun();
    }

    /**
     * 应用切换
     * @param $app
     * @return bool
     * @throws ErrorException
     */
    private function changeApp($app) {
        $root = ConfigManager::load('apps')->item($app);
        if (empty($root)) {
            throw new \ErrorException("应用项目 $app 不存在！");
        }
        //重建符号表
        $this->symbol($app,$root,$root.'/app');
        return true;
    }

    private static function restoreApp() {
        Symbol::$app = '';
        ConfigManager::$app = '';
    }

    private function initApp($app) {
        $this->changeApp($app);
        //配置缓存系统
        $this->cache();
        //安全
        $this->security();
        //配置fapis客户端
        $this->apis();
        //日志功能
        $this->log();
        //配置路由
        $this->setRouter();
        //配置数据库
        $this->db();
        //session共享
        $this->session();
        //模型自动加载
        $this->autoload();
    }

    /**
     * 调试模式（只有在生产环境才关闭）
     */
    private function debugModel() {
        if (Common::isProv()) {
            error_reporting(0);
            return false;
        }
        else {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        }
        return true;
    }

    /**
     * 类自动加载
     */
    private function autoload() {
        $symbol = Symbol::load();
        $bootstrap = $symbol->parse(':bootstrap');
        $appName = ConfigManager::load('control')->item('appname');
        //数据模型类自动加载
        Autoload::create()->psr4([
            'Model\\' => [$bootstrap.'/Model'],
            ucfirst($appName).'\\VM\\' => [$bootstrap.'/VModel'],
            ucfirst($appName).'\\Libs\\' => [$symbol->parse(':libs')],
            ucfirst($appName).'\\App\\' => [$symbol->parse(':resources/application')],
            'App\\Libs\\'=>[$symbol->parse(':libs')]
        ]);
        //配置中类加载
        $autoload = ConfigManager::load('autoload')->item(true,true);
        isset($autoload['psr4'])&&Autoload::create()->psr4($autoload['psr4']);
        isset($autoload['files'])&&Autoload::create()->files($autoload['files']);
    }

    /**
     * 错误提示
     * @return bool
     */
    private function errorTip() {
        if ($this->isCli) return false;
        //加载错误提示
        $whoops = new Run;
        $whoops->pushHandler(new Handler\PrettyPageHandler);
        $whoops->register();
        $whoops = null;
        return true;
    }

    /**
     * 符号表
     * @param string $app 应用项目名称
     * @param string $rootPath 应用所在主目录
     * @param string $appPath 应用app所在目录
     */
    private function symbol($app=null,$rootPath=null,$appPath=null) {
        $rootPath = $rootPath?:ROOT_PATH;
        $appPath = $appPath?:APP_PATH;
        if ($app) {
            Symbol::$app = $app;
            ConfigManager::$app = $app;
        }
        $httpHost = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        //建立符号表
        $symbol = Symbol::load();
        $symbol->add('root',$rootPath);
        $symbol->add('core',CORE_PATH);
        $symbol->add('core.base',CORE_BASE);
        $symbol->add('app',$appPath);
        $symbol->add('assets.local','/app/resources/assets');
        $symbol->add('site.host',$httpHost);
        $symbol->add('site.url',(Common::isHttps()?'https://':'http://').$httpHost);
        $symbol->add(Catalog::path());
        $symbol->save();

        //设置运行变量的符号
        $envVars = $this->getEnv();
        $symbol->add('env',$envVars['env']);
        $symbol->save();
        //运行变量的处理
        $symbol->add('env.path',Common::isProv()?'production':'daily');
        $symbol->save();

        $symbol->add(ConfigManager::load('control')->item('symbol.file'));
        $symbol->add($symbol->parse(':config/symbol.php',true));
        $symbol->save();
        $symbol = null;
    }

    /**
     * 获取运行环境变量
     */
    private function getEnv() {
        return [
            'env'=>getenv('APP_RUN_ENV')
        ];
    }

    /**
     * 设置安全策略
     */
    private function security() {
        Token::setSecreteKey(ConfigManager::load('control')->item('token.secret'));
    }

    /**
     * 设置数据库
     */
    private function db() {
        Db::setConfig(ConfigManager::load('db')->item(true,true));
    }

    /**
     * 设置缓存
     */
    private function cache() {
        $data = ConfigManager::load('cache')->item(true,true);
        Cache::setConfig($data);
    }

    /**
     * 设置session共享
     * @throws ErrorException
     */
    private function session() {
        $name = ConfigManager::load('control')->item('session.name');
        if (!empty($name)) {
            session_name($name);
        }
        if (ConfigManager::load('control')->item('session.share')) {
            $time = ConfigManager::load('control')->item('session.time');
            $gc = ConfigManager::load('control')->item('session.gc');
            SessionManager::$prefix = ConfigManager::load('control')->item('appname');
            new SessionManager(Cache::load('memcache'),Db::load()->connect(),$time,$gc);
        }
        elseif (!isset($_SESSION)){
            session_start();
        }
    }

    /**
     * 模板设置
     */
    private function twig() {
        $symbol = Symbol::load();
        $manager = ConfigManager::load('control');
        $version = new VersionFile;
        $version->add($manager->item('version.file',true));
        $symbol->add('template.version',$version->value());
        $symbol->save();

        ExtendAll::addHelper('symbol',Symbol::load());
        ExtendAll::addHelper('version',$version);
        $config = [
            'views.files.path'=>$symbol->parse(':resources/views'),
            'views.cache.path'=>$symbol->parse(':cache')
        ];
        ExtendAll::setConfig($config);
    }

    /**
     * 路由设置
     */
    private function setRouter() {
        RouterRun::load(Symbol::load()->parse(':resources/router'),Cache::load('memcache','files'));
    }

    /**
     * 路由
     * @param string $uri 输入的url
     * @return array
     */
    private function router($uri='') {
        $app = Request::request('app');
        $act = Request::request('act');
        $uri = $uri?:Request::request('__router');
        $isPost = Common::isPost();
        $isGet = Common::isGet();
        $isCli = Common::isCli();

        //浏览器访问模式,不走路由
        //index.php?app=some&act=some
        ///?app=some&act=some
        if (empty($uri)&&isset($_SERVER['REQUEST_URI'])&&(stripos($_SERVER['REQUEST_URI'],'/?')!==false||stripos($_SERVER['REQUEST_URI'],'/index.php')!==false)) {
            $app===null&&$app='index';
            $act===null&&$act='index';
            if ($isPost) {
                Request::post('app',$app);
                Request::post('act',$act);
            }
            elseif ($isGet||$isCli) {
                Request::get('app',$app);
                Request::get('act',$act);
            }
            Request::request('app',$app);
            Request::request('act',$act);
        }
        else{
            //加载路由
            $router = RouterRun::load();
            //路由匹配
            $DataRouter = $router->get($uri);

            //路由匹配参数写入request
            $all = $DataRouter->get();

            if (is_array($all)) {
                foreach ($all as $k=>$v) {
                    Request::request($k,$v);
                    if ($isPost) {
                        Request::post($k,$v);
                    }
                    elseif ($isGet||$isCli) {
                        Request::get($k,$v);
                    }
                }
            }

            $app = Request::request('app');
            $act = Request::request('act');
        }
        $result['app'] = $app;
        $result['act'] = $act;
        return $result;
    }

    private function partition() {
        if (ConfigManager::exist('partition')) {
            PartitionTableCreate::setConfig(ConfigManager::load('partition')->item(true));
        }
    }

    private function upload() {
        if (ConfigManager::exist('upload')) {
            $config = ConfigManager::load('upload')->item(true,true);
            if (!empty($config['oss'])) {
                Upload::load('oss')->setConfig($config);
            }
            Upload::load()->setConfig($config);
        }
    }

    /**
     * 命令行工具
     * @return bool
     */
    private function cliRun() {
        if (!$this->isCli) return false;

        Console::run('X Command Line Interface','1.00');
        return true;
    }

    /**
     * 日志
     */
    private function log() {
        $loggerEng = ConfigManager::load('control')->item('logger');
        $loggerPath = ConfigManager::load('control')->item('logger.path');
        $loggerError = ConfigManager::load('control')->item('logger.error.enable');
        Logs::setEngine($loggerEng);
        Logs::load()->setBasePath($loggerPath);
        if (Common::isProv()&&$loggerError){
            Logs::addErrorReporting();
        }
    }

    /**
     * 时区
     */
    private function timezone() {
        $timezone = ConfigManager::load('control')->item('timezone');
        $timezone = $timezone?:'prc';
        date_default_timezone_set($timezone);
    }

    private function apis() {
        Autoload::create()->files([
            Symbol::load()->parse(':core/libraries/Fapis/CJsonWebServiceClient.php'),
            Symbol::load()->parse(':core/libraries/Fapis/JsonWebService.php'),
            Symbol::load()->parse(':core/libraries/Fapis/CJsonWebServerReflectionView.php'),
            Symbol::load()->parse(':core/libraries/Fapis/base/CJsonWebServiceImportSecurity.php'),
            Symbol::load()->parse(':core/libraries/Fapis/base/CJsonWebServiceTokenSecurity.php'),
            Symbol::load()->parse(':core/libraries/Fapis/extends/WSImportSecurity.php'),
            Symbol::load()->parse(':core/libraries/Fapis/interface/IJsonWebServiceLog.php'),
            Symbol::load()->parse(':core/libraries/Fapis/interface/IJsonWebServiceIoPretreatment.php'),
            Symbol::load()->parse(':core/libraries/Fapis/interface/IJsonWebServiceVisitPretreatment.php'),
        ]);
        Apis::setConfig(ConfigManager::load('fapis')->item(true,true));
    }

    private function ruleset() {
        \Libs\Ruleset\Base::setPath(Symbol::load()->parse(':bootstrap/Ruleset',true));
    }
}