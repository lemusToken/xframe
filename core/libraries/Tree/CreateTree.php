<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/27
 * Time: 10:07
 */

namespace Libs\Tree;

/**
 * 生成树算法
 * Class CreateTree
 * @package Libs\Tree
 */
class CreateTree {

    /**
     * 生成树
     * @param array $data 数据
     * @param bool $format 是否格式化数据
     * @param int $id id的值，指定id下的树
     * @param string $idStr
     * @param string $pidStr
     * @param string $childStr
     * @return array
     */
    public static function generate($data,$format=false,$id=null,$idStr='id',$pidStr='parent_id',$childStr='children'){
        $items = $format?self::treeFormat($data):$data;
        $tree = [];
        $children = [];
        foreach ($items as $k => $item){
            if (empty($item[$pidStr])) {
                $tree[$k] = &$items[$k];
            }
            else {
                $children[$item[$pidStr]][] = $k;
                $items[$item[$pidStr]][$childStr][$item[$idStr]] = &$items[$k];
            }
        }
        return $id?$items[$id]:$tree;
    }

    /**
     * 根据id查找所有子节点
     * @param $data
     * @param $id
     * @param bool $format
     * @param string $pidStr
     * @return array
     */
    public static function findChildren($data,$id,$format=false,$pidStr='parent_id') {
        $items = $format?self::treeFormat($data):$data;
        $children = [];

        $func = function($loopData,$id,$pidFirst=0) use($pidStr,&$func,&$children){
            if (empty($loopData)) return;
            foreach ($loopData as $k=>$v) {
                if ($v[$pidStr] == $id) {
                    if (!empty($pidFirst)) {
                        $children[$pidFirst][] = $v;
                    }
                    elseif (isset($children[$id])) {
                        $children[$id][] = $v;
                        $pidFirst = $id;
                    }
                    else {
                        $children[$v['id']] = [$v];
                    }
                    unset($loopData[$k]);
                    $func($loopData,$v['id'],$pidFirst);
                }
            }

        };

        $func($items,$id);
        $func = null;
        return $children;
    }

    /**
     * 根据id寻找所有父节点
     * @param $data
     * @param $id
     * @param bool $format
     * @param string $pid
     * @return array
     */
    public static function findParents($data,$id,$format=false,$pid='parent_id') {
        $items = $format?self::treeFormat($data):$data;
        $target = $items[$id];
        $parents = [];

        while (1) {
            if (!empty($target[$pid])) {
                $parents[$target[$pid]] =  $items[$target[$pid]];
                $target = $items[$target[$pid]];
            }
            else {
                break;
            }
        }

        return $parents;
    }

    /**
     * 格式化数据，用id作为数组下标
     * @param $data
     * @param string $id
     * @return array
     */
    private static function treeFormat($data,$id='id') {
        $items = [];
        foreach($data as $v) {
            $items[$v[$id]] = $v;
        }
        return $items;
    }
}