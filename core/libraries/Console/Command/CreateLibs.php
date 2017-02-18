<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;

class CreateLibs extends CommandExtend {
    //指令名称
    protected $name = 'create:libs';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在app文件下生成lib类文件',
            'definition'=>[
                ['n', null, 'VALUE_REQUIRED','lib类名称']
            ],
            'help'=><<<EOT
在当前应用目录下自动生成lib类
EOT
        ];
    }

    /**
     * 指令运行方法
     * @param $input
     * @param $output
     * @param $line
     * @return void
     */
    protected function work($input, $output, $line) {
        $line->newLine();
        $questionHelper = $this->getHelperSet()->get('question');
        $appConfigHelper = $this->getHelper('appConfig');
        $symbol = $this->getHelper('symbol');
        $libsBasePath = $symbol->load()->parse(':libs',true);
        $templateBase = __DIR__.'/../Source';


        $name = $input->getOption('n');

        if (empty($name)) {
            $line->error($this->chs('--n 不可为空！'));
            die;
        }
        $appName = $appConfigHelper->getAppName();

        $namePath = '';

        if (strpos($name,'/')!==false) {
            $ary = explode('/',$name);
            foreach ($ary as &$v) {
                $v = ucfirst($v);
            }
            $name = end($ary);
            $path = implode('\\',$ary);
            $appPath = $libsBasePath.'/'.$path.'.php';
            $namePath = '\\'.dirname($this->formatPath($path));
        }
        else{
            $name = ucfirst($name);
            $appPath = $libsBasePath.'/'.$name.'.php';
        }

        $appPath = $this->formatPath($appPath);

        if (file_exists($appPath)) {
            //交互式输入
            $question = $this->createQuestion($this->chs('系统检测lib文件已存在，是否覆盖[yes/no]?'));
            if (!($questionHelper->ask($input,$output,$question)==='yes')) {
                $line->caution($this->chs("未覆盖文件，程序已退出！"));
                die;
            }
        }

        $context =  file_get_contents($templateBase.'/libs');
        $context = str_replace(['[NAME]','[APPSPACE]','[NAMEPATH]'],[$name,ucfirst($appName),$namePath],$context);
        !file_exists(dirname($appPath))&&mkdir(dirname($appPath),0777,true);
        file_put_contents($appPath,$context)?
            $line->success($this->chs("lib文件:$appPath 生成成功！")):
            $line->error($this->chs("lib文件:$appPath 生成失败！"));
    }
}