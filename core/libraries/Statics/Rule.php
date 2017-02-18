<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/10/9
 * Time: 15:43
 */

namespace Libs\Statics;


class Rule {
    const REG_FILE = '/pack=[\'"]file_(.+?)[\'"]/';
    const REG_IGNORE = '/pack=[\'"]ignore[\'"]/';
    const REG_CLEAN = '/pack=[\'"]clean[\'"]/';

    /**
     * 是否需要忽略打包
     * @param $str
     * @return bool
     */
    public static function isIgnore($str) {
        preg_match(self::REG_IGNORE,$str,$match);
        return !!$match;
    }

    /**
     * 判断是否需要单独打包
     * @param $str
     * @return string
     */
    public static function checkFile($str) {
        preg_match(self::REG_FILE,$str,$match);
        return empty($match[1])?'':$match[1];
    }

    /**
     * 判断是否需要清理
     * @param $str
     * @return string
     */
    public static function checkClean($str) {
        preg_match(self::REG_CLEAN,$str,$match);
        return !!$match;
    }

    /**
     * 规则判断
     * @param $str
     * @return array
     */
    public static function check($str,$all='') {
        $result = [];
        if ($r = self::isIgnore($str)) {
            $result[0] = 'ignore';
        }
        elseif ($all&&$r = self::isIgnore($all)) {
            $result[0] = 'ignore';
        }
        elseif ($r = self::checkFile($str)) {
            $result[0] = 'file';
            $result[1] = $r;
        }
        elseif ($all&&$r = self::checkFile($all)) {
            $result[0] = 'file';
            $result[1] = $r;
        }
        elseif ($r = self::checkClean($str)) {
            $result[0] = 'clean';
        }
        elseif ($all&&$r = self::checkClean($all)) {
            $result[0] = 'clean';
        }
        return $result;
    }
}