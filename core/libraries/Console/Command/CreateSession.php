<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/15
 * Time: 13:00
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class CreateSession extends CommandExtend {
    //指令名称
    protected $name = 'create:session';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'在默认连接的数据库中，生成session数据表',
            'definition'=>[],
            'help'=><<<EOT
生成session数据表
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

        $db = $this->getHelper('db')->getConnection();
        $database = $db->getDatabase();

        $templateBase = __DIR__.'/../Source';
        $type = 'sql';

        $table = $db->fetchColumn('SELECT table_name FROM information_schema.TABLES WHERE TABLE_NAME ="session" and TABLE_SCHEMA="'.$database.'"');

        if ($table) {
            $line->note($this->chs("session表已经存在！"));
            die;
        }

        $sql = file_get_contents("$templateBase/$type/dbsession.sql");
        $db->executeUpdate($sql);

        $line->success($this->chs("session表生成成功！"));
    }
}