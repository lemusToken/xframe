<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;
use Libs\Application\Check;
use Libs\Utils\Url as UrlBase;


class StaticPack extends CommandExtend {
    //指令名称
    protected $name = 'pack';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'对页面中的静态资源进行打包',
            'definition'=>[
                ['url', null, 'VALUE_REQUIRED','访问的页面地址']
            ],
            'help'=><<<EOT
对访问页面中的静态资源进行打包
EOT
        ];
    }

    /**
     * 指令运行方法
     * @param $input
     * @param $output
     * @param $line
     * @return bool
     */
    protected function work($input, $output, $line) {
        $line->newLine();
        $helperUrl = $this->getHelper('url');
        $helperPack = $this->getHelper('pack');
        $appConfig = $this->getHelper('appConfig');
        $helperSyms = $this->getHelper('symbol')->load();

        $url = $input->getOption('url');

        if (empty($url)) {
            $line->error($this->chs('--url 不可为空！'));
            die;
        }

        $urlData = $helperUrl->parse($url);
        //获取查询路径（路由）
        //定义资源存放目录
        $basePath = $helperSyms->parse(':root:assets.local/dst');

        //改写地址，添加dev参数
        //保证每次运行都是开发环境
        Check::dst('dev');
        $url = UrlBase::create($url,[Check::getParamName('dst')=>'dev'],'&');

        //访问并返回页面
        $body = $helperUrl->curl($url);

        //获取app和act，并且更新查询参数
        $appAct = $helperUrl->runRouter($urlData['path']);

        $staticFiles = [];
        $staticList = [];

        //更新或者创建资源文件
        //合并压缩资源
        if ($body) {
            //生成静态资源，并且返回资源列表
            $staticData = $helperPack->create($body,$url,$basePath,[
                'app'=>$appAct['app'],
                'act'=>$appAct['act'],
                'appname'=>strtolower($appConfig->getAppName()),
                'subappname'=>Check::app()
            ]);
            $staticList = $staticData['list'];
            $staticFiles = $staticData['res'];
        }

        //更新或者创建模板文件
        //取得原文件模板
        //获取模板字符串（未解析）
        $content = $helperUrl->getView($appAct['app'],$appAct['act']);

        if (empty($content)) return false;
        //解析所有子模板，获取完整（整合）模板，解析load
        $templateAll = $this->loadTemplate($content['content'],$content['path']['base']);
        //镜像复制所有的引用模板
        $this->findAllTwig($templateAll,$content['path']['base'],'dst',$helperPack);

        $template = $this->changeToDst($content['content']);
        $template = $helperPack->cleanAll($template);

        //所有的ignore列表
        $ignoreAll = $helperPack->getIgnore($body);

        //添加ignore的资源
        $ignoreList = ['css'=>'','js'=>''];
        if (!empty($ignoreAll[0])) {
            foreach ($ignoreAll[0] as $val) {
                //判断是否重复
                if (stripos($val,'.js')!==false&&!in_array($helperPack->getUrl($val,$url),$staticList)) {
                    $ignoreList['js'] .= "\n".$val."\n";
                }
                elseif (stripos($val,'.css')!==false&&!in_array($helperPack->getUrl($val,$url),$staticList)) {
                    $ignoreList['css'] .= "\n".$val."\n";
                }
                elseif (stripos($val,'<style')!==false) {
                    $ignoreList['css'] .= "\n".$val."\n";
                }
                elseif (stripos($val,'<script')!==false) {
                    $ignoreList['js'] .= "\n".$val."\n";
                }
            }
        }

        $mainFileName = [];
        //添加独立资源
        $assetsLocal = $helperSyms->parse(':root:assets.local');
        foreach ($staticFiles as $k=>$v) {
            if (strpos($k,'.main.min.js')!==false) {
                $mainFileName['js'] = $k;
                continue;
            }
            elseif (strpos($k,'.main.min.css')!==false) {
                $mainFileName['css'] = $k;
                continue;
            }
            if (strpos($k,'.js')!==false&&!in_array($helperPack->getUrl($v,$url),$staticList)) {
                $v = "{{ ':assets".str_replace($assetsLocal,'',$v)."'|static|raw }}";
                $ignoreList['js'] .= "\n".$v."\n";
            }
            elseif(strpos($k,'.css')!==false&&!in_array($helperPack->getUrl($v,$url),$staticList)) {
                $v = "{{ ':assets".str_replace($assetsLocal,'',$v)."'|static|raw }}";
                $ignoreList['css'] .= "\n".$v."\n";
            }
        }

        //添加主资源
        //main.min.js
        $ignoreList['js'] .= empty($mainFileName['js'])||empty($staticFiles[$mainFileName['js']])?'':"\n{{ ':assets".str_replace($assetsLocal,'',$staticFiles[$mainFileName['js']])."'|static|raw }}\n";
        //main.css.js
        $ignoreList['css'] .= empty($mainFileName['css'])||empty($staticFiles[$mainFileName['css']])?'':"\n{{ ':assets".str_replace($assetsLocal,'',$staticFiles[$mainFileName['css']])."'|static|raw }}\n";

        //替换:assets.local为:assets
        $ignoreList['js'] = str_replace(':assets.local',':assets',$ignoreList['js']);
        $ignoreList['css'] = str_replace(':assets.local',':assets',$ignoreList['css']);

        //代码中添加js和css
        $template = $helperPack->addJS($template,$ignoreList['js']);
        $template = $helperPack->addCSS($template,$ignoreList['css']);

        //生成合并后的模板
        $viewFilePath = $content['path']['base'].'/dst/'.(str_replace($appAct['app'].'/',$appAct['app'].'/',$content['path']['file']));

        if(!is_dir(dirname($viewFilePath))) {
            mkdir(dirname($viewFilePath),0777,true);
        }

        file_put_contents($viewFilePath,$template);
        return true;
    }

    protected function loadTemplate($content,$base='') {
        $helperUrl = $this->getHelper('url');
        preg_match_all('/\{\{\s*?load\(\'(.*?)\'\)\|raw\s*?\}\}/',$content,$match);
        if (!empty($match[1])&&is_array($match[1])) {
            foreach ($match[1] as $k=>$v) {
                //解析路由
                Check::dst('dev');
                $appAct = $helperUrl->runRouter($v);
                $viewData = $helperUrl->getView($appAct['app'],$appAct['act']);
                $file = "{#'{$viewData['path']['file']}'#}";
                $file .= "\n{% load '$v' %}\n{$viewData['content']}\n{% endload %}\n";
                $content = str_replace($match[0][$k],$file,$content);
                $content = $this->loadTemplate($content,$base);
            }
        }
        return $content;
    }

    protected function findAllTwig($str,$base,$dst,$helper) {
        preg_match_all('/[\'"](.*?\.twig)[\'"]/',$str,$match);
        if (is_array($match[1])) {
            foreach ($match[1] as $k=>$v) {
                $f = $base.'/'.$v;
                if (file_exists($f)) {
                    //获取模板字符串
                    $content = file_get_contents($f);
                    //清除静态资源
                    $content = $helper->cleanAll($content);
                    $this->findAllTwig($content,$base,$dst,$helper);

                    $content = $this->changeToDst($content);
                    //在目的地址生成新的twig
                    $f = $base.'/'.$dst.'/'.$v;
                    if(!is_dir(dirname($f))) {
                        mkdir(dirname($f),0777,true);
                    }
                    file_put_contents($f,$content);
                }
            }
        }
    }

    protected function changeToDst($content) {
        //所有的载入的地址后面加dst/
        $content = preg_replace_callback('/[\'"](.*?\.twig)[\'"]/',function($m){
            return str_replace($m[1],'dst/'.$m[1],$m[0]);
        },$content);
        return $content;
    }
}