<?php
namespace Libs\VModel;
use \Libs\VModel\Base;
/**
 * 数据库的表对象操作层
 * <li>对表对象的操作进行最大程度的优化，提高开发效率</li>
 * @author JerryLi
 *
 */
abstract class BaseTable extends Base{
    protected $aField = array();
    /**
     * 数据库操作对象
     * @var \Doctrine\DBAL\Connection
     */
    protected $oDb = null;
    /**
     * 主表的名称
     * @var String
     */
    protected $sTabN = null;
    /**
     * 构造函数
     * @param string $aMasterTableName 主表的表名
     * @param array $aMastFields 主表字段说明数组
     * <li>array('字段名'=>'字段的说明', ...)</li>
     * @param string $sDbCfgName 数据库配置名
     */
    public function __construct($aMasterTableName, $aMastFields, $sDbCfgName){
        if (empty($aMasterTableName)){ //必须配置主表的字段说明
            throw new \ErrorException('主表表名必须提供');
        }elseif (empty($aMastFields)){ //必须配置主表的字段说明
            throw new \ErrorException('主表的字段配置说明$aMastFields必须提供');
        }elseif (empty($sDbCfgName)){ //数据库配置名必须提供
            throw new \ErrorException('数据库配置名$sDbCfgName必须提供');
        }
        $this->oDb = $this->db($sDbCfgName); //建立数据库连接保存引用
        $this->aField = array_keys($aMastFields);//取出字段名数组
        $this->sTabN = $aMasterTableName;
    }
    /**
     * 获取整个记录集
     * @param string|array $statement The SQL query to be executed
     * @param array $params The prepared statement params
     * <li>例如: [':id'=>$mIdVal, ...]</li>
     * @param array $types The query parameter types
     * <li>例如：[\PDO::PARAM_INT,\PDO::PARAM_INT]</li>
     * @return array(array('field'=>'val',...),...)
     */
    protected function find($statement, $params=array(), $types=array()){
        if (is_array($statement)){
            $statement = implode($statement);
        }
        return $this->oDb->fetchAll($statement, $params, $types);
    }
    /**
     * 获取整个记录集（分页处理）
     * <li>注意：输入的$statement中不要带 LIMIT，否则SQL会出错</li>
     * @param string|array $statement The SQL query to be executed
     * @param array $params The prepared statement params
     * <li>例如: [':id'=>$mIdVal, ...]</li>
     * @param array $types The query parameter types
     * <li>例如：[\PDO::PARAM_INT,\PDO::PARAM_INT]</li>
     * @param int $iPage 当前页码
     * <li>默认为1，必须大于1否则强转1</li>
     * @param int $iPagesize 页大小
     * <li>默认为10，必须大于1否则强转1</li>
     * @return array(array('field'=>'val',...),...)
     */
    protected function findPage($statement, $params=array(), $types=array(), $iPage=1, $iPagesize=10){
        if (is_array($statement)){
            $statement = implode($statement);
        }
        //输入数据的整理矫正
        $iPage = intval($iPage);
        $iPagesize = intval($iPagesize);
        if ($iPage <= 1){ //页码矫正
            $iPage = 1;
        }
        if ($iPagesize <= 1){ //页大小矫正
            $iPagesize = 1;
        }
        //分页算法处理
        if (1 === $iPage){ //取第一页数据的处理
            $statement .= ' LIMIT '. $iPagesize;
        }else{ //取出其他页的处理
            $statement .= ' LIMIT '. (($iPage - 1) * $iPagesize) .','. intval($iPagesize);
        }
        return $this->oDb->fetchAll($statement, $params, $types); //取分页信息
    }
    /**
     * 获取单行记录
     * @param string|array $statement The SQL query to be executed
     * @param array $params The prepared statement params
     * <li>例如: [':id'=>$mIdVal, ...]</li>
     * @param array $types The query parameter types
     * <li>例如：[\PDO::PARAM_INT,\PDO::PARAM_INT]</li>
     * @return false | array('field'=>'...',...)
     */
    protected function get_row($statement, $params=array(), $types=array()){
        if (is_array($statement)){
            $statement = implode($statement);
        }
        if (false !== stripos($statement, ' LIMIT ')){ //检查是否存在LIMIT
            $statement .= ' LIMIT 1'; //不存在LIMIT默认强制只返回第一行
        }
        return $this->oDb->fetchAssoc($statement, $params, $types);
    }
    /**
     * 获取统计值
     * <li>直接返回第一条记录的第一个字段值，且此字段值一定为数字</li>
     * @param string|array $statement The SQL query to be executed.
     * @param array $params The prepared statement params.
     * @return int
     */
    protected function getCnt($statement, $params=array()){
        if (is_array($statement)){
            $statement = implode($statement);
        }
            if (false !== stripos($statement, ' LIMIT ')){ //检查是否存在LIMIT
            $statement .= ' LIMIT 1'; //不存在LIMIT默认强制只返回第一行
        }
        return intval($this->oDb->fetchColumn($statement, $params, 0));
    }
    /**
     * 批量插入
     * @param array $aFields 插入的字段名数组
     * @param array $aVals 插入的数据集
     * <li>二维数据集array(array('val', 'val', ...), ...)</li>
     * @param string $sTable 表名
     * <li>默认为''，使用当前主表表名</li>
     * @return int 影响条数
     */
    protected function insertBatch($aFields, $aVals, $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        //生成插入模板
        $aSql = 'INSERT INTO '. $sTable .' ('. implode(',', $aFields) .') VALUE('. implode(',', array_fill(0, count($aFields), '?') ) .')';
        $insertStatement = $this->oDb->prepare($aSql);//预编译模板
        $this->oDb->beginTransaction();//开启事务
        $iRowCnt = 0;
        foreach ($aVals as $aVal){
            $insertStatement->execute($aVal); //灌入记录集
            $iRowCnt += $insertStatement->rowCount(); //记录影响条数
        }
        $this->oDb->commit(); //提交记录
        return $iRowCnt;//返回影响总条数
    }
    /**
     * 单行记录插入（延迟插入方式）
     * <li>插入后返回自增id</li>
     * @param array $aFieldVals 插入的字段名与值
     * <li>array('field'=>'val',...)</li>
     * @param string $sTable 表名
     * <li>默认为''，使用当前主表表名</li>
     * @return boolean true:提交成功|false:提交失败
     */
    protected function insertDelay($aFieldVals, $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        //生成插入模板
        $sSql = 'INSERT DELAYED INTO '. $sTable .' ('. implode(',', array_keys($aFieldVals)) .
                ') VALUE('. implode(',', array_fill(0, count($aFieldVals), '?') ) .')';
        return $this->oDb->prepare($sSql)->execute(array_values($aFieldVals)); //预编译 | 插入记录集
    }
    /**
     * 单行记录插入(替换形式的插入操作REPLACE INTO)
     * <li>插入后返回自增id</li>
     * @param array $aFieldVals 插入的字段名与值
     * <li>array('field'=>'val',...)</li>
     * @param string $sTable 表名
     * <li>默认为''，使用当前主表表名</li>
     * @return mixed 自增id值
     */
    protected function insertReplace($aFieldVals, $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        //生成插入模板
        $sSql = 'REPLACE INTO '. $sTable .' ('. implode(',', array_keys($aFieldVals)) .
                ') VALUE('. implode(',', array_fill(0, count($aFieldVals), '?') ) .')';
        $this->oDb->prepare($sSql)->execute(array_values($aFieldVals)); //预编译 | 插入记录集
        return $this->oDb->lastInsertId();//返回插入的自增id
    }
    /**
     * 单行记录插入
     * <li>插入后返回自增id</li>
     * @param array $aFieldVals 插入的字段名与值
     * <li>array('field'=>'val',...)</li>
     * @param string $sTable 表名
     * <li>默认为''，使用当前主表表名</li>
     * @return mixed 自增id值
     */
    protected function insert($aFieldVals, $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        //生成插入模板
        $sSql = 'INSERT INTO '. $sTable .' ('. implode(',', array_keys($aFieldVals)) .
                ') VALUE('. implode(',', array_fill(0, count($aFieldVals), '?') ) .')';
        $this->oDb->prepare($sSql)->execute(array_values($aFieldVals)); //预编译 | 插入记录集
        return $this->oDb->lastInsertId();//返回插入的自增id
    }
    /**
     * 单表跟新记录
     * @param int $aSetFieldVals 跟新的值
     * <li>array('field'=>'修改的值', ...)</li>
     * @param string $sWhere 条件
     * <li>注意：传入的外部参数必须以 占位符方式，并通过$params传入占位符的值</li>
     * @param array $aWhereParams The prepared statement params
     * <li>例如: [':id'=>$mIdVal, ...]</li>
     * @param string $sTable 表名
     * <li>默认为''，使用当前主表表名</li>
     * @return int 影响的条数
     */
    protected function update($aSetFieldVals, $sWhere, $aWhereParams=array(), $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        
        $aSets = array();
        $aParam = array(); //参数替换符变量
        foreach ($aSetFieldVals as $sKey => $sVal){
            $aSets[] = $sKey .'= :sk_'. $sKey;
            $aParam[':sk_'.$sKey] = $sVal;
        }
        if (!empty($aWhereParams)){
            $aParam = array_merge($aParam, $aWhereParams); //合并where的替换符参数
        }
        //生成查询语句
        $aSql = array();
        $aSql[] = 'UPDATE '. $sTable;
        $aSql[] =   ' SET '. implode(',', $aSets);
        $aSql[] = ' WHERE '. $sWhere;
        $oSmt = $this->oDb->prepare(implode($aSql)); //编译SQL
        $oSmt->execute($aParam); //执行
        return $oSmt->rowCount(); //返回影响的记录条数
    }
    /**
     * 单表删除记录
     * @param string $sWhere 条件
     * <li>注意：传入的外部参数必须以 占位符方式，并通过$params传入占位符的值</li>
     * @param array $params The prepared statement params
     * <li>例如: [':id'=>$mIdVal, ...]</li>
     * @param string $sTable
     * <li>默认为''，使用当前主表表名</li>
     * @return int 影响的条数
     */
    protected function delete($sWhere, $params=array(), $sTable=''){
        $sTable = (empty($sTable) ? $this->sTabN : $sTable);
        $aSql = array();
        $aSql[] = 'DELETE FROM '. $sTable;
        $aSql[] = ' WHERE '. $sWhere;
        $oSmt = $this->oDb->prepare(implode($aSql)); //编译SQL
        if (empty($params)){
            $oSmt->execute(); //执行
        }else{
            $oSmt->execute($params); //执行
        }
        return $oSmt->rowCount(); //返回影响的记录条数
    }
    /**
     * 取出指定id的记录详情（公共封装方法）
     * @param mixed $mIdVal id值
     * @param array $aFields 输出的字段
     * @param string $sIdFieldName 指定id字段
     * <li>默认为id，如果不是id字段，请自行制定</li>
     * @return false|array()
     */
    protected function get_info_by_id($mIdVal, $aFields=array(), $sIdFieldName='id'){
        $aFields = (empty($aFields)? $this->aField : $aFields);
        $aSql = array();
        $aSql[] = 'SELECT '. implode(',', $aFields);
        $aSql[] =  ' FROM '. $this->sTabN;
        $aSql[] = ' WHERE '. $sIdFieldName .'=:id';
        $aSql[] = ' LIMIT 1';
        return $this->oDb->fetchAssoc(implode($aSql), [':id'=>$mIdVal]);
    }
}
