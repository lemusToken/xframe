<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/23
 * Time: 14:37
 */

namespace Libs\PartitionTable;


abstract class PartTable {

    /**
     * 从配置中生成sql语句
     * @param $fieldsData array 字段信息
     * @param $tableName string 表名
     * @param array $tableData 表的配置
     * @param boolean $checkExist 是否进行表存在判断
     * @return string
     * @author le.xu
     */
    public function createTableSql($fieldsData=[],$tableName='',$tableData=[],$checkExist=true){
        $string = $checkExist?"CREATE TABLE IF NOT EXISTS `{$tableName}` (":
            "DROP TABLE IF EXISTS `{$tableName}`;CREATE TABLE `{$tableName}` (";
        $fields = [];
        $table = [
            'engine'=>empty($tableData['engine'])?'InnoDB':$tableData['engine'],
            'auto_inc'=>empty($tableData['auto_inc'])?0:(int)$tableData['auto_inc'],
            'charset'=>empty($tableData['charset'])?'utf8':$tableData['charset'],
            'comment'=>empty($tableData['comment'])?'':$tableData['comment']
        ];
        foreach ($fieldsData as $v){
            $fields[] = self::_createField($v);
        }

        if (!empty($tableData['primary'])) {
            $f = "PRIMARY KEY (";
            $t = [];
            foreach ($tableData['primary'] as $v) {
                $t[] = "`$v`";
            }
            $f .= implode(',',$t).')';
            $fields[] = $f;//主键
        }
        else {
            $fields[] = "PRIMARY KEY (`{$fieldsData[0]['name']}`)";//主键
        }
        if (!empty($tableData['key'])){
            foreach ($tableData['key'] as $kk=>$vv){
                $fields[] = "KEY `{$kk}` (`".str_replace(',','`,`',$vv)."`) USING BTREE";
            }
        }

        $string .= implode(',',$fields).')';
        $string .= "ENGINE={$table['engine']} AUTO_INCREMENT={$table['auto_inc']} DEFAULT CHARSET={$table['charset']} COMMENT='".$table['comment']."';";

        return $string;
    }

    /**
     * @param array $data
     * <p>[
     *      ['name'=>'字段名',
     *      'type'=>'字段类型',
     *      'charset'=>'字符集,false不设字符',
     *      'charset_order'=>'字符集排序规则',
     *      'unsigned'=>'无符号',
     *      'default'=>'默认值,false则为NULL',
     *      'increment'=>'自增',
     *      'comment'=>'注释'],...
     * ]</p>
     * @return string
     * @author le.xu
     */
    private static function _createField($data=[]){
        $string = [];

        $string[] = "`{$data['name']}`";//字段名
        $string[] = "{$data['type']}";//字段类型
        $string[] = !isset($data['charset'])?'':(empty($data['charset'])?"CHARACTER SET gbk COLLATE gbk_chinese_ci":
            "CHARACTER SET {$data['charset']} COLLATE {$data['charset_order']}");//字段编码
        $string[] = isset($data['unsigned'])?"UNSIGNED":'';//无符号
        $string[] = !isset($data['default'])?"NULL":(
        $data['default']===false?'NOT NULL':(
        is_string($data['default'])?"NOT NULL DEFAULT '{$data['default']}'":
            "NOT NULL DEFAULT {$data['default']}"
        )
        );//默认值
        $string[] = isset($data['increment'])?'AUTO_INCREMENT':'';//自增
        $string[] = empty($data['comment'])?'':"COMMENT '{$data['comment']}'";//注释

        return implode(' ',$string);
    }

    abstract public function createTable($table,$data,$checkExist=true);
}