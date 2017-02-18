<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/9/16
 * Time: 1:08
 */

namespace Libs\Utils\Cryption;


class Exor {

    /**
     * 异或加解密
     * @param string $key 密钥
     * @param string $str 明文或者密文
     * @param string $char 编码
     * @return string
     */
    public static function code($key,$str,$char='utf-8'){
        $str = array_values(unpack('n*', mb_convert_encoding($str,'UCS-2','utf-8')));
        $key = array_values(unpack('n*', mb_convert_encoding($key,'UCS-2',$char)));

        $res = '';
        $totalKeyVal = array_sum($key);

        foreach($str as $v) {
            $res .= pack('n', $v ^ $totalKeyVal);
        }
        return mb_convert_encoding($res,$char,'UCS-2');
    }
}