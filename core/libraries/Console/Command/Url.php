<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class Url extends CommandExtend {

    protected $name = 'url';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'用于cli访问app或者curl模拟访问',
            'definition'=>[
                ['l', null, 'VALUE_REQUIRED','所需要访问的地址'],
                ['t', null, 'VALUE_OPTIONAL','curl模式下的访问方式(get|post)','get'],
                ['p', null, 'VALUE_NONE','curl模式下是否显示页面结果'],
            ],
            'help'=><<<EOT
当地址中存在//，将直接用curl模式模拟访问
例如：url --l "http://www.something.com/a/b" --t get

当地址中不存在//，将直接通过路由模式解析
例如：url --l "a/b"
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
        $url = $input->getOption('l');
        $type = $input->getOption('t');
        $needPrint = $input->getOption('p');

        $helper = $this->getHelper('url');
        $info = $helper->parse($url);
        if (empty($info['host'])) {
            $helper->router($url);
        }
        else {
            $helper->curl($url,$type,$needPrint);
        }
    }
}