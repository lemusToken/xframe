<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;

class UpdatePartition extends CommandExtend {
    //指令名称
    protected $name = 'update:part';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'更新各个分表信息',
            'arguments'=>[
                ['sql', 'VALUE_REQUIRE','sql语句']
            ],
            'definition'=>[
            ],
            'help'=><<<EOT
根据输入的sql语句，更新指定表的所有分表信息
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

        $sql = $input->getArgument('sql');
        $db = $this->getHelper('db')->getConnection();

        if (empty($sql)) {
            $line->error($this->chs('sql语句不可为空！'));
            die;
        }
        $partition = $this->getHelper('part');
        $sqls = $partition->updateSql($sql);

        foreach ($sqls as $k=>$v) {
            $db->executeUpdate($v);
        }
    }
}