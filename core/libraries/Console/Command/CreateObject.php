<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class CreateObject extends CommandExtend {

    protected $name = 'create:object';
    private $templateBase= '';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'生成应用项目文件',
            'definition'=>[
                ['p', null, 'VALUE_OPTIONAL','项目所在目录'],
                ['n', null, 'VALUE_OPTIONAL','项目名称，生成的项目目录与框架目录同级']
            ],
            'help'=><<<EOT
在指定目录下，生成完整的应用项目
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
        $this->templateBase = __DIR__.'/../Source';
        $line->newLine();
        $questionHelper = $this->getHelperSet()->get('question');

        $path = $input->getOption('p');
        $n = $input->getOption('n');
        if (empty($path)&&!empty($n)) {
            $path = '../../../'.$n;
        }
        if (empty($path)) {
            $line->error($this->chs('项目名称或者路径不可为空！'));
            die;
        }
        $appName = basename($path);
        $appSpace = ucfirst($appName);

        if (strpos($appName,'-')!==false) {
            $tmp = explode('-',$appName);
            foreach ($tmp as &$v) {
                $v = ucfirst($v);
            }
            $appSpace = implode('',$tmp);
        }

        if (is_dir($path)) {
            $question = $this->createQuestion($this->chs('系统检测目录已存在，是否覆盖[yes/no]，覆盖前请先备份已有工作内容?'));
            if (!($questionHelper->ask($input,$output,$question)==='yes')) {
                $line->caution($this->chs("用户取消，程序已退出！"));
                die;
            }
        }

        $data = [];
        $this->scandirDeep($this->templateBase.'/object',$data);

        //倒叙生成文件夹
        $len = count($data['folder']);
        for ($i=$len-1;$i>=0;$i--) {
            $v = str_replace($this->templateBase.'/object',$path,$data['folder'][$i]);
            if (!file_exists($v)) {
                mkdir($v,0777);
            }
        }
        $line->success($this->chs('应用架构生成成功！'));

        foreach($data['file'] as $v) {
            $context = file_get_contents($v);
            $name = basename($v);
            if ($name==='index__php') {
                $context = str_replace(['[CORE_PATH]'],["'".realpath(__DIR__.'/../../../')."'"],$context);
            }
            elseif ($name==='control__php') {
                $context = str_replace(['[TOKENKEY]','[APPSPACE]'],["'".substr(md5(mt_rand(1,1000000)),0,8)."'","'".$appSpace."'"],$context);
            }
            elseif (in_array($name,['Demo__php','Index__php','Vmodel__php','App__php','Test__php',
                'Symbol__php','Db__php','Config__php','Cache__php','App__php','Encrypt__php', 'ExampleTable__php'])) {
                $context = str_replace(['[APPSPACE]'],[$appSpace],$context);
            }
            $vv = str_replace([$this->templateBase.'/object','__'],[$path,'.'],$v);
            file_put_contents($vv,$context);
        }
        $line->success($this->chs('应用文件生成成功！'));
        $line->success($this->chs('应用框架全部建立完成！','所在路径：',realpath($path)));
    }
}