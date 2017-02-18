<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2017/1/4
 * Time: 16:31
 */

namespace Libs\Utils\Interpreter\Partition;
use Libs\Utils\Singleton\InstanceTrait;

/**
 * 将字符串根据标签进行分区
 * Class Main
 * @package Libs\Utils\Interpreter\Partition
 */
class Main {
    use InstanceTrait;

    private $tags=[];

    /**
     * 设置开始和结束标签
     */
    public function setTag($tags) {
        if (is_array($tags)) {
            $this->tags = array_merge($this->tags,$tags);
        }
        else {
            $this->tags[] = $tags;
        }
    }

    public function scan($str) {
        $strln = strlen($str);
        $step = 0;
        $result = ['word'=>[],'tag'=>[],'text'=>[]];
        $index =0;
        $lastI = 0;
        for ($i=0;$i<$strln;$i++) {
            $isMatch = false;
            foreach ($this->tags as $v) {
                if ($this->checkEdge($v,$str,$i)) {
                    $lv = strlen($v);
                    if ($step>0) {
                        $vv = $index === 0?[0,$step]:[$i-$step,$step];
                        $result['word'][$index] = $vv;
                        $result['text'][] = $vv;
                    }

                    $vv = [$i,$lv];
                    $result['text'][] = $vv;
                    $result['tag'][$index] = $vv;

                    $step = 0;
                    $i += $lv-1;
                    $lastI = $i+1;
                    $index += 1;
                    $isMatch = true;
                    break;
                }
                else {
                    $isMatch = false;
                }
            }
            !$isMatch&&$step += 1;
        }

        if ($lastI<$strln) {
            $vv = [$lastI,$strln-$lastI];
            $result['word'][] = $vv;
            $result['text'][] = $vv;
        }
        return $result;
    }

    public function scanStr($str) {
        $parts = $this->scan($str);
        $reuslt = ['text'=>[],'word'=>[]];
        $len = count($parts['text']);
        for ($i=0;$i<$len;$i++) {
            $reuslt['text'][] = substr($str,$parts['text'][$i][0],$parts['text'][$i][1]);
            if (isset($parts['word'][$i])) {
                $reuslt['word'][] = substr($str,$parts['word'][$i][0],$parts['word'][$i][1]);
            }
        }
        return $reuslt;
    }

    /**
     * 判断边界字符
     * @param $char
     * @param $str
     * @param int $index
     * @return false|int
     */
    private function checkEdge($char,$str,$index=0) {
        $len = strlen($char);
        $s = '';
        for ($i=$len-1;$i>=0;$i--) {
            if (!isset($str{$index+$i})) {
                return false;
            }
            $s .= $str{$index+$len-$i-1};
        }

        return $char===$s;
    }

}