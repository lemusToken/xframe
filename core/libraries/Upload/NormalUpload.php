<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/16
 * Time: 17:55
 */

namespace Libs\Upload;

class NormalUpload  extends Upload {

    /**
     * 文件上传
     * @param string $name 传输名称
     * @param string $saveDir 保存文件的路径
     * @param string|callable $newFileName 新的文件名，默认空，如果是random则名称随机
     * @return array|bool
     */
    public function upload($name,$saveDir,$newFileName='') {
        if (empty($_FILES[$name])) return false;
        $file = $_FILES[$name];//得到传输的数据

        $result = [];

        $moveFileFn = function($tmpName,$name,$saveDir,$newFileName){
            $r = '';
            $newPath = realpath($saveDir).DIRECTORY_SEPARATOR.urlencode($newFileName?:$name);
            if (!is_uploaded_file($tmpName)) {
                $r = false;
            }
            elseif (move_uploaded_file($tmpName,$newPath)) {
                $r = $newPath;
            }
            return $r;
        };

        if (is_array($file['name'])) {
            foreach ($file['name'] as $k=>$v) {
                $fileNew = $newFileName==='random'?self::createRandomName($v):$newFileName;
                $r = $moveFileFn($file['tmp_name'][$k],$v,$saveDir,$fileNew);
                if (!$r) {
                    continue;
                }
                $result[] = $r;
            }
        }
        else {
            $result[] = $moveFileFn($file['tmp_name'],$file['name'],$saveDir,$newFileName);
        }

        return $result;
    }

    public function delete($file) {
        return unlink($file);
    }
}