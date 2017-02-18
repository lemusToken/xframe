<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/10/17
 * Time: 10:09
 */
namespace Libs\Fapis;

class Apis {
    /**
     * JsonWebServiceClient操作单例对象
     * @var \CJsonWebServiceClient
     */
    private static $instant=null;
    private static $config = [];

    /**
     * FAPIS接口通信函数
     * @param string $sPackage 包名
     * @param string $sClass 类名
     * @param array $aParam 需要提交的参数
     * <li>注意：输入内容为本地字符集</li>
     * @param boolean $bDebug 临时开启一次调试模式
     * @return int|array 0:通信失败 | -1:解析Json失败 | array():正常返回
     * <li>注意：输出内容为本地字符集</li>
     */
    public static function client($sPackage, $sClass, $aParam, $bDebug=false){
        if (empty(self::$config['client.file'])||empty(self::$config['root'])){
            return 0;
        }
        if (is_null(self::$instant)){ //单例不存在，建立缓存
            $sCfgPath = self::$config['root'] . '/app/libs/jsonWebService/config/' . self::$config['client.file']; //载入配置
            self::$instant = new \CJsonWebServiceClient($sCfgPath);
        }
        return self::$instant->exec($sPackage, $sClass, $aParam, $bDebug); //远程调用FAPIS接口
    }

    public static function setConfig($cfg) {
        self::$config = $cfg;
    }
}