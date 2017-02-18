<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;

class CreateApp extends CommandExtend {
    //指令名称
    protected $name = 'create:app';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在app文件下生成app类文件',
            'definition'=>[
                ['n', null, 'VALUE_REQUIRED','app类名称'],
                ['v', null, 'VALUE_OPTIONAL','是否生成对应的view文件','yes']
            ],
            'help'=><<<EOT
在当前应用目录下自动生成app类文件和对应的view文件
可以指定--v false，不自动生成view文件
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
        $appBasePath = $symbol->load()->parse(':resources/application',true);
        $viewBasePath = $symbol->load()->parse(':resources/views',true);


        $name = $input->getOption('n');

        if (empty($name)) {
            $line->error($this->chs('--n 不可为空！'));
            die;
        }
        $appName = $appConfigHelper->getAppName();

        $needView = $input->getOption('v');
        $templateBase = __DIR__.'/../Source';

        $namePath = '';
        $subAppPath = '';
        $firstSubAppName = '';

        if (strpos($name,'/')!==false) {
            $ary = explode('/',$name);
            foreach ($ary as &$v) {
                $v = ucfirst($v);
            }

            $name = end($ary);
            $path = implode('\\',$ary);
            $appPath = $appBasePath.'/'.$path.'.php';
            $firstSubAppName = '\\'.$ary[0];
            $subAppPath = $appBasePath.'/'.$ary[0];
            $viewPath = $viewBasePath.'/'.strtolower($path).'/index.twig';
            $namePath = '\\'.dirname($this->formatPath($path));
        }
        else{
            $name = ucfirst($name);
            $viewPath = $viewBasePath.'/'.strtolower($name).'/index.twig';
            $appPath = $appBasePath.'/'.$name.'.php';
        }

        $appPath = $this->formatPath($appPath);
        $viewPath = $this->formatPath($viewPath);

        if (file_exists($appPath)) {
            //交互式输入
            $question = $this->createQuestion($this->chs('系统检测app文件已存在，是否覆盖[yes/no]?'));
            if (!($questionHelper->ask($input,$output,$question)==='yes')) {
                $line->caution($this->chs("未覆盖文件，程序已退出！"));
                die;
            }
        }

        $template = [
            'app'=>empty($subAppPath)?$templateBase.'/app':$templateBase.'/subapp',
            'appWithoutView'=>empty($subAppPath)?$templateBase.'/appWithoutView':$templateBase.'/subappWithoutView',
        ];

        //需要生成对应的view文件
        if ($needView==='yes') {
            $context =  file_get_contents($template['app']);
            !file_exists(dirname($viewPath))&&mkdir(dirname($viewPath),0777,true);
            file_put_contents($viewPath,' ')?
                $line->success($this->chs("view文件:$viewPath 生成成功！")):
                $line->error($this->chs("view文件:$viewPath 生成失败！"));
        }
        else {
            $context =  file_get_contents($template['appWithoutView']);
            $line->caution($this->chs("已忽略生成view文件！"));
        }

        $context = str_replace(['[NAME]','[APPSPACE]','[NAMEPATH]','[FIRSTNAME]'],[$name,ucfirst($appName),$namePath,$firstSubAppName],$context);
        !file_exists(dirname($appPath))&&mkdir(dirname($appPath),0777,true);

        file_put_contents($appPath,$context)?
            $line->success($this->chs("app文件:$appPath 生成成功！")):
            $line->error($this->chs("app文件:$appPath 生成失败！"));

        if (!empty($subAppPath)) {
            $subAppPath = $this->formatPath($subAppPath.'/App.php');

            if (!file_exists($subAppPath)) {
                $context =  file_get_contents($templateBase.'/subappbase');
                $context = str_replace(['[NAME]','[APPSPACE]','[NAMEPATH]','[FIRSTNAME]'],[$name,ucfirst($appName),$namePath,$firstSubAppName],$context);
                file_put_contents($subAppPath,$context)?
                    $line->success($this->chs("app基类文件:$subAppPath 生成成功！")):
                    $line->error($this->chs("app基类文件:$subAppPath 生成失败！"));
            }
        }
    }
}