<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console\Command;
use \Libs\Console\Command\Extend\CommandExtend;


class CreateMap extends CommandExtend {
    //指令名称
    protected $name = 'create:map';

    /**
     * 指令配置
     * @return array
     */
    protected function config() {
        return [
            'description'=>'从默认连接的数据库中，生成数据库词典文件',
            'definition'=>[
                ['n', null, 'VALUE_OPTIONAL','表名']
            ],
            'help'=><<<EOT
将当前数据库各数据表的结构导入到词典文件中
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
        $symbol = $this->getHelper('symbol');
        $templateBase = __DIR__.'/../Source';
        $type = 'html';
        $content = file_get_contents("$templateBase/maps/$type/content");
        $map = file_get_contents("$templateBase/maps/$type/map");
        $appMapPath = $symbol->load()->parse(':bootstrap/Maps/map.html',true);

        $dbName = $db->fetchAll('select database() db');
        $status = $db->fetchAll('show table status');

        $mapData = [];
        $contentData = [
            'dbname'=>$dbName[0]['db'],
            'content'=>''
        ];

        $trList = [];
        $duplicate = [];
        foreach ($status as $k=>$v) {
            $list = $db->fetchAll("select COLUMN_NAME,IS_NULLABLE,COLUMN_TYPE,COLLATION_NAME,COLUMN_DEFAULT,
COLUMN_KEY,EXTRA,COLUMN_COMMENT from information_schema.COLUMNS where table_name = '{$v['Name']}' and table_schema = '{$contentData['dbname']}'");
            $mapData['table_name'] = $v['Name'];
            $mapData['table_engine'] = $v['Engine'];
            $mapData['table_char'] = $v['Collation'];
            $mapData['table_desc'] = $v['Comment'];
            $mapData['tr'] = '';
            foreach ($list as $vv) {
                $mapData['tr'] .= "
                <tr>
                    <td>{$vv['COLUMN_NAME']}</td>
                    <td>{$vv['COLUMN_TYPE']}</td>
                    <td>{$vv['COLLATION_NAME']}</td>
                    <td>{$vv['IS_NULLABLE']}</td>
                    <td>{$vv['COLUMN_KEY']}</td>
                    <td>{$vv['COLUMN_DEFAULT']}</td>
                    <td>{$vv['EXTRA']}</td>
                    <td>{$vv['COLUMN_COMMENT']}</td>
                </tr>";
            }

            //表结构重复
            if ($i=array_search($mapData['tr'],$trList)) {
                $duplicate[$i][] = $v['Name'];
            }
            else {
                $trList[$v['Name']] = $mapData['tr'];
                $contentData['content'] .= self::value2PlaceHolder($map,$mapData);
            }
        }

        $contentData['duplicate'] = '';
        foreach ($duplicate as $k=>$v) {
            $contentData['duplicate'] .= "<tr><td>{$k}</td><td>";
            foreach ($v as $vv) {
                $contentData['duplicate'] .= '<span id="'.$vv.'">'.$vv.' ';
            }
            $contentData['duplicate'] .= '</td></tr>';
        }

        file_put_contents($appMapPath,self::value2PlaceHolder($content,$contentData))?
            $line->success($this->chs("文件:{$appMapPath} 生成成功！")):
            $line->error($this->chs("文件生成失败！"));
    }

    private static function value2PlaceHolder($str,$data) {
        $keys = array_keys($data);
        foreach ($keys as &$v) {
            $v = '['.strtoupper($v).']';
        }
        return str_replace($keys,array_values($data),$str);
    }
}