<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/23
 * Time: 13:57
 */

namespace Libs\Application;
use Libs\Config\ConfigManager;
use Libs\TwigExtend\ExtendAll;
use Libs\TwigExtend\Extend\ExtendGlobal;
use Libs\Utils\Common;
use Libs\Utils\Request;
use Libs\Utils\Symbol\Symbol;
use Libs\VModel\Base as VMBase;
use Libs\VModel\BaseTable;

/**
 * 控制器基类
 * Class Base
 * @package Libs\Application
 */
class Base {

    private $autoload = true;
    private $onlyFile = false;
    private $onlyTemplate = false;
    private $onlyData = [];
    private $tempAppAct = [];
    private $jsData = [];
    private $jsStr = '';
    private $cssStr = '';
    private static $assetsData = [];
    private static $assetDataExist = [];
    private static $classMap = [];
    private static $urlCurrent = '';

    public function __construct() {
        $this->init();
        $urlCurrent = self::getCurrentUrl();
        self::$assetsData[$urlCurrent] = isset(self::$assetsData[$urlCurrent])?self::$assetsData[$urlCurrent]:[];
        self::$classMap = self::$classMap?:array_map(function($m){
            return strtolower($m);
        },get_class_methods(__CLASS__));
    }

    protected function init() {}

    public static function getCurrentUrl() {
        return self::$urlCurrent = self::$urlCurrent?:Common::currentUrl();
    }

    /**
     * 渲染模板
     **/
    public function render() {
        $args = func_get_args();
        $twig = ExtendAll::load($this->autoload);
        $viewPath = $twig->getLoader()->getPaths();

        $file = $this->getRenderFile(isset($args[0])?$args[0]:'',$viewPath[0]);
        $isDstFile = is_string($file)&&strpos($file,'dst/')!==false;
        $params = [];
        if (isset($args[0])&&is_array($args[0])) {
            $params = $args[0];
        }
        elseif (isset($args[1])&&is_array($args[1])) {
            $params = $args[1];
        }
        if ($this->onlyData===true) {
            return $params;
        }
        elseif ($this->onlyFile===true) {
            $this->renderInit();
            $content = $twig->render($file,$params);
            if (!empty($this->jsData)||!empty($this->jsStr)) {
                $content = $this->renderWithJsAndCss($content,$isDstFile);
            }
            return $content;
        }
        elseif ($this->onlyTemplate===true) {
            $this->renderInit();
            return ['path'=>['base'=>$viewPath[0],'file'=>$file],'content'=>$this->renderWithJsAndCss(file_get_contents($viewPath[0].'/'.$file),$isDstFile)];
        }
        else {
            $content = $twig->render($file,$params);
            if (!empty($this->jsData)||!empty($this->jsStr)) {
                $content = $this->renderWithJsAndCss($content,$isDstFile);
            }
            if (!empty(self::$assetsData)) {
                $content = $this->renderWithAssets($content);
            }
            echo $content;
        }

        $this->renderInit();
        die;
    }

    /**
     * 加载控制器
     * @param $name string 控制器名称
     * @param $act string 方法
     * @throws \Exception
     * @return bool
     **/
    public static function run($name,$act,$isOnlyFile=false,$isOnlyData=false,$isOnlyTemplate=false) {
        $path = Symbol::load()->parse(':resources/application',true);
        $appName = ConfigManager::load('control')->item('appname');

        //子应用名称
        $subAppName = Check::app();

        include_once $path.'/App.php';
        $name = $subAppName?ucfirst($subAppName).'/'.ucfirst($name):ucfirst($name);
        if (!$name||!file_exists($path.'/'.$name.'.php')) {
            self::error("app 文件:$name.php 不存在!");
        }
        else {
            include_once $path.'/'.$name.'.php';
        }

        //如果$name是路径，转成命名空间
        if (strpos($name,'/')!==false) {
            $name = str_replace('/','\\',$name);
        }

        $name = ucfirst($appName).'\\App\\'.ucfirst($name);
        $ist = new $name;
        //all
        if (method_exists($name,'all')&&$ist->all()===false) {
            return false;
        }
        //方法不存在以及排除自有关键词
        if (!method_exists($name,$act)||in_array(strtolower($act),self::$classMap)) {
            self::error("app 文件:$name.php 中 $act 方法不存在!");
        }
        $app = new $name;
        //装饰
        if ($isOnlyData) {
            $app->findOnlyData($isOnlyData);
            return $app->$act();
        }
        elseif ($isOnlyFile) {
            $app->renderOnlyFile($isOnlyFile);
            return $app->$act();
        }
        elseif ($isOnlyTemplate) {
            $app->renderOnlyTemplate($isOnlyTemplate);
            return $app->$act();
        }
        $app->$act();
        return true;
    }

    /**
     * 缓存资源
     * @param $v
     */
    public static function saveExistAsset($v) {
        self::$assetDataExist[] = $v;
    }

    public static function getExistAssets() {
        return self::$assetDataExist;
    }

    public static function getAssets() {
        return self::$assetsData[self::$urlCurrent];
    }

    /**
     * 判断资源是否存在
     * @param $v
     * @return bool
     */
    public static function isExistAsset($v) {
        return in_array($v,self::getExistAssets());
    }

    public static function clearAssets() {
        self::$assetsData[self::$urlCurrent] = [];
        self::$assetDataExist = [];
    }

    /**
     * 获取模板名称
     * @param $f
     * @return array|string
     */
    protected function getRenderFile($f,$baseView='') {
        $file = $f;
        if (!empty($this->onlyData)) {
            $file = $this->onlyData;
        }
        elseif (empty($file)||!is_string($file)){
            $app = Request::request('app');
            $act = Request::request('act');
            $file = "$app/$act";
        }
        elseif (is_string($file)&&strpos($file,'/')!==0){
            $app = Request::request('app');
            $file = "$app/$file";
        }
        if (is_string($file)) {
            $sp = Check::app();
            $file = ($sp?strtolower($sp).'/':'').$file;
            if (!empty($baseView)&&Check::dst()!=='dev') {
                $dst = 'dst/'.$file.'.twig';
                if (file_exists($baseView.'/'.$dst)) {
                    $file = $dst;
                }
                else {
                    $file .= '.twig';
                }
            }
            else {
                $file = $file.'.twig';
            }
        }

        return $file;
    }

    /**
     * 添加资源
     * @param array $list
     * @return bool
     */
    protected function assets($list) {
        if (empty($list)) return false;
        $urlCurrent = self::getCurrentUrl();
        if (isset(self::$assetsData[$urlCurrent])) {
            self::$assetsData[$urlCurrent] = array_merge(self::$assetsData[$urlCurrent],$list);
        }
        else {
            self::$assetsData[$urlCurrent] = $list;
        }
        return true;
    }

    /**
     * 记录js代码或者变量
     * @param $in
     * @return bool
     */
    protected function js($in) {
        if (empty($in)) return false;
        if (is_array($in)) {
            $this->jsData = array_merge($this->jsData,$in);
        }
        else {
            $this->jsStr .= $in;
        }
        return true;
    }

    /**
     * 记录css代码
     * @param $in
     * @return bool
     */
    protected function css($in) {
        if (empty($in)) return false;
        $this->cssStr .= $in;
        return true;
    }

    /**
     * 创建script标签
     * @param $src
     * @param string $version
     * @param array $attr
     * @return string
     */
    protected function createJs($src,$version='',$attr=[]) {
        $attrList = [];
        foreach ($attr as $k=>$v) {
            $attrList[] = $k.'="'.$v.'"';
        }
        return '<script src="'.$this->addVersion($src,$version).'" '.implode(' ',$attrList).' ></script>';
    }

    /**
     * 创建link标签
     * @param $src
     * @param string $version
     * @param array $attr
     * @return string
     */
    protected function createCss($src,$version='',$attr=[]) {
        $attrList = [];
        foreach ($attr as $k=>$v) {
            $attrList[] = $k.'="'.$v.'"';
        }
        return '<link rel="stylesheet" href="'.$this->addVersion($src,$version).'" '.implode(' ',$attrList).'>';
    }

    /**
     * 添加版本号
     * @param $src
     * @param $version
     * @return mixed|string
     */
    protected function addVersion($src,$version) {
        if (empty($version)) return $src;
        if (strpos($src,'?')!==false){
            $src .= "&v=$version";
        }
        else if (($p=strpos($src,'#'))!==false){
            $j = substr($src,$p+1);
            $src = str_replace("#$j","?v=$version#{$j}",$src);
        }
        else{
            $src .= "?v=$version";
        }
        return $src;
    }

    /**
     * 渲染后，添加script和link代码
     * @param $content
     * @return mixed|string
     */
    protected function renderWithAssets($content) {
        $assets = self::getAssets();
        if (empty($assets)) return $content;
        $version = $this->symbol()->parse(':template.version',true);
        $js = '';
        $css = '';
        foreach ($assets as $v) {
            $v = $this->symbol()->parse($v);
            if (self::isExistAsset($v)) {
                continue;
            }
            self::saveExistAsset($v);
            if (stripos($v,'.js')!==false) {
                $js .= $this->createJs($v,$version);
            }
            //css
            elseif (stripos($v,'.css')!==false) {
                $css .= $this->createCss($v,$version);
            }
        }
        $content = $this->addInFooter($content,$js);
        $content = $this->addInHeader($content,$css);
        self::clearAssets();
        return $content;
    }

    /**
     * 渲染后，添加js和css代码
     * @param $content
     * @return mixed|string
     */
    protected function renderWithJsAndCss($content,$isDst=false) {
        if (!empty($this->cssStr)) {
            $content = $this->addInHeader($content,"<style>".$this->cssStr."</style>");
        }
        if (!empty($this->jsData)) {
            $r = Common::randStr(6);
            $code = '';
            foreach($this->jsData as $k=>$v) {
                $v = is_string($v)?"'$v'":(is_array($v)?json_encode($v):$v);
                $k = is_string($k)?"'$k'":$k;
                $code .= "window[window['{keycode}']][$k]=$v;";
            }
            $content = $this->addInHeader($content,"<script pack=\"clean\">(function(){window['{keycode}']=window['{keycode}']||'$r';window['$r']=window['$r']||{};".$code."})();</script>");
        }
        if (!empty($this->jsStr)&&!$isDst) {
            $content = $this->addInFooter($content,"<script>(function(){".$this->jsStr."})()</script>");
        }

        return $content;
    }

    /**
     * 添加在头部
     * @param $content
     * @param $str
     * @return mixed|string
     */
    protected function addInHeader($content,$str) {
        $result = '';
        if (stripos($content,'</head>')!==false) {
            $result = str_replace('</head>',$str."\n</head>",$content);
        }
        elseif (stripos($content,'<body>')!==false) {
            $result = str_replace('<body>',"<body>\n".$str,$content);
        }
        elseif (stripos($content,'<html>')!==false) {
            $result = str_replace('<html>',"<html>\n".$str,$content);
        }
        elseif (($p=strpos($content,$r='{% block assets_header %}'))!==false) {
            //匹配block
            $ps = Common::matchStartEnd($content,'{% block','{% endblock %}');
            foreach ($ps as $k=>$v) {
                if (substr($content,$v[0],strlen($r))===$r) {
                    $_p = $v[1]-strlen('{% endblock %}');
                    $result = substr($content,0,$_p).$str.substr($content,$_p);
                    break;
                }
            }
        }
        else {
            $result = $str.$content;
        }
        return $result;
    }

    /**
     * 添加在尾部
     * @param $content
     * @param $str
     * @return string
     */
    protected function addInFooter($content,$str) {
        $result = '';
        if (($p=stripos($content,$r='</body>'))!==false) {
            $result = substr($content,0,$p).$str.substr($content,$p);
        }
        elseif (($p=stripos($content,$r='</html>'))!==false) {
            $result = substr($content,0,$p).$str.substr($content,$p);
        }
        elseif (($p=strpos($content,$r='{% block assets_footer %}'))!==false) {
            //匹配block
            $ps = Common::matchStartEnd($content,'{% block','{% endblock %}');
            foreach ($ps as $k=>$v) {
                if (substr($content,$v[0],strlen($r))===$r) {
                    $_p = $v[1]-strlen('{% endblock %}');
                    $result = substr($content,0,$_p).$str.substr($content,$_p);
                    break;
                }
            }
        }
        else {
            $result = $content.$str;
        }
        return $result;
    }

    protected function validate($rulesetName,$data,$options=[]) {
        return \Libs\Ruleset\Base::validateRuleSet($rulesetName,$data,$options);
    }

    /**
     * 获取本地vm实例
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    protected function vm($name) {
        $vm = VMBase::run($name);
        if ($vm instanceof BaseTable){ //限制不能在application中实例化BaseTable的子类
            throw new \ErrorException('不能在Application层中实例化继承与 \Libs\VModel\BaseTable 的子类');
        }
        return $vm;
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
     * 添加twig模板的全局变量
     * @return ExtendGlobal
     */
    protected function extendGlobal() {
        return new ExtendGlobal;
    }

    /**
     * 是否自动更新模板
     * @param bool $v
     */
    protected function renderAuto($v) {
        $this->autoload = $v;
    }

    /**
     * 是否只返回页面数据
     * @param bool $v
     */
    protected function findOnlyData($v) {
        $this->onlyData = $v;
    }

    /**
     * 是否返回解析后的模板，但是不echo输出
     * @param bool $v
     */
    protected function renderOnlyFile($v) {
        $this->onlyFile = $v;
    }

    /**
     * 是否返回模板路径信息和未解析的模板源码
     * @param bool $v
     */
    protected function renderOnlyTemplate($v) {
        $this->onlyTemplate = $v;
    }

    /**
     * 渲染初始化
     */
    private function renderInit() {
        Check::app('');
        Check::dst('');
        $this->renderAuto(true);
        $this->renderOnlyFile(false);
        $this->tempAppAct = [];
        $this->renderOnlyTemplate(false);
        $this->findOnlyData(false);
    }

    private static function error($str) {
        if (Common::isProv()) {
            header('HTTP/1.1 404 Not Found');
            header('status: 404 Not Found');
            $twig = ExtendAll::load(false);
            echo $twig->render('error/404.twig');
        }
        else {
            throw new \ErrorException($str);
        }
    }
}