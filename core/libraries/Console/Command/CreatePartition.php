<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;

class CreatePartition extends CommandExtend {
    //指令名称
    protected $name = 'create:part';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在数据主库生成分表',
            'definition'=>[
                ['type', null, 'VALUE_OPTIONAL','分表类型','partition'],
            ],
            'help'=><<<EOT
根据分表配置，在数据主库生成分表
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

        $type = $input->getOption('type');

        $db = $this->getHelper('db')->getConnection();
        $partition = $this->getHelper('part');

        $sqls = $partition->getSql($type);

        if ($type==='partition') {
            foreach ($sqls as $k=>$v) {
                $db->executeUpdate($v);
            }
        }
        elseif ($type==='subtable') {
            foreach ($sqls as $v) {
                foreach ($v as $vv) {
                    $db->executeUpdate($vv);
                }
            }
        }
    }
}