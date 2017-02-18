<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/10/9
 * Time: 15:43
 */

namespace Libs\Statics;

/**
 * 查找页面中的静态资源
 * Class Find
 * @package Libs\Statics
 */
class Find {

    //查找所有css和js
    const REG_JC = '/<link.*?href=["\'](.*?)["\']>|<style.*?>([\s\S]*?)<\/style>|<script.*?src=["\'](.*?)["\']><\/script>|<script.*?>([\s\S]*?)<\/script>/';
    //查找所有css和js以及资源表达式
    const REG_JCS = '/(?:<link.*?href=["\'].*?["\']>|<style.*?>[\s\S]*?<\/style>|<script.*?src=["\'].*?["\']><\/script>|<script.*?>[\s\S]*?<\/script>|\{\{.*?\|static.*?\}\})[\r\n]{0,2}/';
    const REG_JSC_URL = '/<link.*?href=["\'](.*?)["\']>|<script.*?src=["\'](.*?)["\']><\/script>|\{\{.*?["\'](.*?)["\']\|static.*?\}\}/';
    //查找所有的ignore
    const REG_IGNORE = '/<link href=".*?pack=ignore.*?">|<script src=".*?pack=ignore.*?"><\/script>|<style.*?pack="ignore".*?>[\s\S]*?<\/style>|<script.*?pack="ignore".*?>[\s\S]*?<\/script>|\{\{.*?pack=ignore[^A-Za-z0-9]*?\|static.*?\}\}/';
    const REG_CLEAN = '/[\?#].*?$/';

    public static function jsAndCss($str) {
        preg_match_all(self::REG_JC,$str,$match);
        return $match;
    }

    public static function clean($str) {
        return preg_replace(self::REG_CLEAN,'',$str);
    }

    public static function cleanAll($str) {
        return preg_replace(self::REG_JCS,'',$str);
    }

    public static function findIgnore($str) {
        preg_match_all(self::REG_IGNORE,$str,$match);
        return $match;
    }

    public static function findUrl($str,$clean=false) {
        if (empty($str)) return '';
        preg_match_all(self::REG_JSC_URL,$str,$match);
        $result = '';
        if (!empty($match)) {
            $result =
                empty($match[1][0])?(
                    (empty($match[2][0])?
                        (empty($match[3][0])?'':$match[3][0]):$match[2][0]
                    )):$match[1][0];
        }
        return $clean?self::clean($result):$result;
    }

    /**
     * 合并
     * @param $str
     * @return array
     */
    public static function combine($str,$host='') {
        $match = self::jsAndCss($str);
        $result = [];
        if (empty($match)) return $result;

        //用于去除重复
        $temp = ['js'=>[],'css'=>[]];
        //合并css
        foreach ($match[1] as $k=>$v) {
            $v = !empty($v)?$v:(!empty($match[2][$k])?$match[2][$k]:'');
            if (empty($v)) continue;
            $isFile = in_array($v,$match[1]);
            if ($isFile) {
                $v = self::resourcePath(self::clean($v),$host);
                if (in_array($v,$temp['css'])) continue;
                $temp['css'][] = $v;
            }
            $r = Rule::check($v,$match[0][$k]);
            $s = isset($r[0])?$r[0]:'';
            switch ($s) {
                case 'ignore';
                    continue;
                case 'file':
                    $result['css'][$r[1]][] = $isFile?file_get_contents($v):$v;
                    break;
                case 'clean':
                    break;
                default:
                    $result['css']['main'][] = $isFile?file_get_contents($v):$v;
                    break;
            }
        }

        //合并js
        foreach ($match[3] as $k=>$v) {
            $v = !empty($v)?$v:(!empty($match[4][$k])?$match[4][$k]:'');
            if (empty($v)) continue;
            $isFile = in_array($v,$match[3]);
            if ($isFile) {
                $v = self::resourcePath(self::clean($v),$host);
                if (in_array($v,$temp['js'])) continue;
                $temp['js'][] = $v;
            }
            $r = Rule::check($v,$match[0][$k]);
            $s = isset($r[0])?$r[0]:'';

            switch ($s) {
                case 'ignore';
                    continue;
                case 'file':
                    $result['js'][$r[1]][] = ';'.($isFile?file_get_contents($v):$v);
                    break;
                case 'clean':
                    break;
                default:
                    $result['js']['main'][] = ';'.($isFile?file_get_contents($v):$v);
                    break;
            }
        }
        $result['list'] = $temp;
        unset($temp);
        $temp = null;
        return $result;
    }

    public static function resourcePath($path,$host='') {
        if (empty($host)||stripos($path,'http:')!==false||stripos($path,'https:')!==false) {
            return $path;
        }
        if (strpos($path,'/')===0) {
            $p = strpos($host,'//');
            $p = strpos($host,'/',$p+2);
            $p!==false&&$host = substr($host,0,$p);
            $path = substr($path,1);
        }
        elseif ($p=strrpos($host,'/')) {
            $host = substr($host,0,$p);
        }
        return $host.'/'.$path;
    }
}