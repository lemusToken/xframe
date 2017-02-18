<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/16
 * Time: 17:55
 */

namespace Libs\Upload;

use Libs\Utils\Common;
use Libs\Utils\Singleton\InstanceTrait;

class Upload {
    use InstanceTrait;

    public $config=[];
    private $allTypes=[];

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }

    public static function load($type='_',$config=[]) {
        $inst = self::loadInst($type);
        if (empty($inst->config)) {
            return $inst;
        }
        switch ($type) {
            case 'oss':
                $config['accessKeyId'] = isset($inst->config['oss']['access.keyid'])?$inst->config['oss']['access.keyid']:$config['accessKeyId'];
                $config['accessKeySecret'] = isset($inst->config['oss']['access.keysecret'])?$inst->config['oss']['access.keysecret']:$config['accessKeySecret'];
                $config['endpoint'] = isset($inst->config['oss']['endpoint'])?$inst->config['oss']['endpoint']:$config['endpoint'];
                $config['bucket'] = isset($inst->config['oss']['bucket'])?$inst->config['oss']['bucket']:$config['bucket'];
                $s = new OssUpload($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint'],$config['bucket']);
                $s->setConfig($inst->config);
                break;
            default:
                $s = new NormalUpload;
                $s->setConfig($inst->config);
                break;
        }
        return $s;
    }

    /**
     * 校验
     * @param $file
     * @param array $options
     * @return array
     */
    public function validate($file,$options=[]) {
        $result = [
            'status'=>1,
            'error'=>''
        ];
        if (!$this->validateSize($file)) {
            $result['status'] = 0;
            $result['error'] = 'ErrorSize';
        }
        elseif (!$this->validateType($file,isset($options['type'])?$options['type']:'')) {
            $result['status'] = 0;
            $result['error'] = 'ErrorType';
        }
        return $result;
    }

    /**
     * 校验文件类型
     * @param $file
     * @param string $group
     * @return bool
     */
    public function validateType($file,$group='') {
        $types = null;
        if (empty($this->allTypes)) {
            $types = $this->config['file.type'];
            foreach ($types as $k=>$v) {
                $this->allTypes = array_merge($this->allTypes,$v);
            }
            $this->allTypes = array_unique($this->allTypes);
        }
        if (!empty($group)) {
            $types = $this->config['file.type'][$group];
        }
        else {
            $types = $this->allTypes;
        }
        return self::checkFileType($file,$types);
    }

    /**
     * 校验文件大小
     * @param $file
     * @return bool
     */
    public function validateSize($file) {
        if (empty($this->config['file.maxsize'])) return true;
        return self::checkFileSize($file,$this->config['file.maxsize']);
    }

    public static function getHost($url) {
        $p=strpos($url,'://');
        if ($p!==false) {
            $url = substr($url,$p+3);
            $p = strpos($url,'/');
            $url = substr($url,$p);
        }
        return $url;
    }

    /**
     * 检测文件名是否在合法范围内
     * @param $file
     * @param $securePath
     * @return bool
     */
    public static function checkFileSecure($file,$securePath) {
        $result = false;
        if (is_string($securePath)) {
            $result = strpos($file,$securePath)!==false;
        }
        elseif (is_array($securePath)) {
            foreach ($securePath as $v) {
                if (strpos($file,$v)!==false) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    public static function checkFileSize($file,$max) {
        $size = filesize($file);
        return $size>=$max;
    }

    public static function checkFileType($file,$types=[]) {
        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)),$types);
    }

    /**
     * 创建随机文件名
     * @param $file
     * @param string $dir
     * @return string
     */
    public static function createRandomName($file,$dir='') {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return $dir.date('Ymd.His', time()).'.'.Common::randStr(4).'.'.$ext;
    }
}