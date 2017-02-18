<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class CreateModel extends CommandExtend {
    //指令名称
    protected $name = 'create:model';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在app文件下生成model类文件',
            'definition'=>[
                ['n', null, 'VALUE_OPTIONAL','元数据名称'],
                ['r', null, 'VALUE_NONE','是否重新生成实体类，注意原数据会被覆盖'],
                ['u', null, 'VALUE_NONE','更新实体类'],
            ],
            'help'=><<<EOT
当前应用目录下，通过分析Maps文件夹下的yml元数据,自动生成model类文件
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

        $name = $input->getOption('n');
        $regenerate = $input->getOption('r');
        $update = $input->getOption('u');
        $command = [
            'php run orm:generate:entities',
            '--generate-annotations true'
        ];
        if (!empty($name)) {
            $command[] = '--filter "Model\\\\'.$name.'"';
        }
        if ($regenerate) {
            $command[] = '--regenerate-entities true';
        }
        if ($update) {
            $command[] = '--update-entities true';
        }

        $command[] = '"../bootstrap"';
        system(implode(' ',$command));
    }
}