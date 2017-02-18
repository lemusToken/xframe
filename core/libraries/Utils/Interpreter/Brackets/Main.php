<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/9
 * Time: 17:10
 */

namespace Libs\Utils\Interpreter\Brackets;
use \Libs\Utils\Singleton\InstanceTrait;


class Main {
    //单例
    use InstanceTrait;

    private $flag = false;
    private $index = 0;
    private $indexes = [];
    private $position = [];
    private $tags = [];
    private $tagsExclude = [];

    /**
     * 设置开始和结束标签
     * @param $start
     * @param $end
     */
    public function setTag($start,$end) {
        if (isset($this->tags[$start])&&is_array($this->tags[$start])) {
            if (is_array($end)) {
                $this->tags[$start] = array_merge($this->tags[$start],$end);
                $this->tags[$start] = array_unique($this->tags[$start]);
            }
            elseif (!in_array($end,$this->tags[$start])) {
                $this->tags[$start][] = $end;
            }
        }
        elseif (isset($this->tags[$start])&&is_string($this->tags[$start])) {
            if (is_array($end)) {
                $this->tags[$start] = array_merge([$this->tags[$start]],$end);
                $this->tags[$start] = array_unique($this->tags[$start]);
            }
            elseif ($this->tags[$start]!==$end) {
                $this->tags[$start] = [$this->tags[$start],$end];
            }
        }
        else {
            $this->tags[$start] = $end;
        }
    }

    /**
     * 设置排除标签
     * @param $tag
     * @param $val
     */
    public function setExclude($tag,$val) {
        $this->tagsExclude[$tag] = $val;
    }

    public function mapTags() {
        return $this->tags;
    }

    public function scan($str,$tag=[]) {
        $this->position = [];
        $len = strlen($str);
        $tags = [];
        if (!empty($tag)) {
            foreach ($tag as $v) {
                $tags[$v] = $this->tags[$v];
            }
        }
        else {
            $tags = $this->tags;
        }

        for ($i=0;$i<$len;$i++) {
            $this->flag = false;
            foreach ($tags as $t=>$l) {
                $this->scanStart($t,$str,$i);
                $this->scanEnd($t,$str,$i);
            }
        }

        return $this->position;
    }

    public function scanStr($str,$tag=[],$position=[]) {
        if (empty($position)) {
            $position = $this->scan($str,$tag);
        }
        $result = [];
        foreach ($position as $char=>$v) {
            foreach ($v as $kk=>$vv) {
                if (!isset($vv[1])) continue;
                $result[$char][] = substr($str,$vv[0],$vv[1]-$vv[0]+1);
            }
        }
        return $result;
    }

    protected function scanStart($char,$str,$i) {
        if ($this->flag||!($p = $this->isEdge($char,$str,$i))) return false;
        $this->indexes[$char][] = $this->index;
        $this->position[$char][$this->index][] = $p[0];
        $this->index += 1;
        return true;
    }

    protected function scanEnd($char,$str,$i) {
        if (!isset($this->indexes[$char])||!($p = $this->isEdge($this->tags[$char],$str,$i))) return false;
        $n = array_pop($this->indexes[$char]);
        $this->position[$char][$n][] = $p[1]===''?$i:$i-1;
        $this->flag = true;
        return true;
    }

    /**
     * 判断是否是边界字符
     * @param $char
     * @param $str
     * @param int $index
     * @return false|array
     */
    public function isEdge($char,$str,$index=0) {
        if (is_array($char)) {
            return $this->checkEdges($char,$str,$index);
        }
        $p = $this->checkEdge($char,$str,$index);
        return $p?[$p,$char]:false;
    }

    /**
     * 判断边界字符
     * @param $char
     * @param $str
     * @param int $index
     * @return false|int
     */
    private function checkEdge($char,$str,$index=0) {
        if ($char==='') {
            return $index===strlen($str)-1?$index:false;
        }

        $len = strlen($char);

        $s = '';
        for ($i=$len-1;$i>=0;$i--) {
            if (!isset($str{$index+$i})) {
                return false;
            }
            $s .= $str{$index+$len-$i-1};
        }

        if ($char===$s) {
            //排除
            if (!empty($this->tagsExclude[$char])) {
                foreach ($this->tagsExclude[$char] as $ev) {
                    if (substr($str,$index-1,2)===$ev) {
                        return false;
                    }
                }
            }

            return $index+$len;
        }

        return false;
    }

    private function checkEdges(array $chars,$str,$index=0) {
        foreach ($chars as $e) {
            if ($i=$this->checkEdge($e,$str,$index)) {
                return [$i,$e];
            }
        }
        return false;
    }
}