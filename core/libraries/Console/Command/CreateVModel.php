<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class CreateVModel extends CommandExtend {
    //指令名称
    protected $name = 'create:vmodel';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在app文件下生成vmodel类文件',
            'definition'=>[
                ['n', null, 'VALUE_REQUIRED','vmodel类名称'],
                ['tab', null, 'VALUE_OPTIONAL','创建对table操作的vmodel类','no'],
            ],
            'help'=><<<EOT
在当前应用目录下自动生成vmodel类文件
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
        $symbol = $this->getHelper('symbol');
        $appConfigHelper = $this->getHelper('appConfig');
        $vmodelBasePath = $symbol->load()->parse(':bootstrap/VModel',true);

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
            $vmodelPath = $vmodelBasePath.'/'.$path.'.php';
            $namePath = '\\'.dirname($this->formatPath($path));
        }
        else{
            $name = ucfirst($name);
            $vmodelPath = $vmodelBasePath.'/'.$name.'.php';
        }

        $templateBase = __DIR__.'/../Source';
        $vmodelPath = $this->formatPath($vmodelPath);
        $base = dirname($vmodelPath);
        if (!file_exists($base)) {
            mkdir($base,0777,true);
        }

        $vmodelPath = $this->formatPath($vmodelPath);

        if (file_exists($vmodelPath)) {
            //交互式输入
            $question = $this->createQuestion($this->chs('系统检测vmodel文件已存在，是否覆盖[yes/no]?'));
            if (!($questionHelper->ask($input,$output,$question)==='yes')) {
                $line->caution($this->chs("未覆盖文件，程序已退出！"));
                die;
            }
        }
        
        $sTemplateFileName = 'vmodel';
        if ('yes' === $input->getOption('tab')){
            $sTemplateFileName = 'vmodelBaseTable';
        }
        $context = str_replace(['[NAME]','[APPSPACE]','[NAMEPATH]'],[$name,ucfirst($appName),$namePath],
            file_get_contents($templateBase.'/'. $sTemplateFileName));
        file_put_contents($vmodelPath,$context)?
            $line->success($this->chs("vmodel文件:$vmodelPath 生成成功！")):
            $line->error($this->chs("vmodel文件:$vmodelPath 生成失败！"));
    }
}