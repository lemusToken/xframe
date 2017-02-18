<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/16
 * Time: 17:55
 */

namespace Libs\Upload;

use \OSS\OssClient;


class OssUpload extends Upload {
    private $accessKeyId = '';
    private $accessKeySecret = '';
    private $endpoint = '';
    private $bucket = '';
    private $client = null;

    public function __construct($accessKeyId,$accessKeySecret,$endpoint,$bucket='') {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
        $this->client = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
    }
    /**
     * 上传文件到alioss上
     * @param string $localFile 本地文件路径
     * @param string $dir 图片上传后的路径
     * @return array
     */
    public function upload($localFile,$dir='') {
        $dir = empty($dir)?(isset($this->config['oss']['folder'])?$this->config['oss']['folder']:''):$dir;
        $result = $this->client->uploadFile($this->getBucket(),$this->createRandomName($localFile,$dir),$localFile);
        return $result;
    }

    /**
     * 删除oss上的具体文件（需要账号权限）
     * @param $ossFile
     * @param bool $securePath
     * @return bool|null
     */
    public function delete($ossFile,$securePath=true) {
        $securePath = $securePath===true?$this->config['oss']['folder']:$securePath;
        if (!self::checkFileSecure($ossFile,$securePath)) return false;
        $result = $this->client->deleteObject($this->getBucket(), $ossFile);
        return $result;
    }

    public function getClientImgHost() {
        if (!isset($this->config['oss']['client.image.host'])) return '';
        $host = $this->config['oss']['client.image.host'];
        if (is_array($host)) {
            $r = rand(0,count($host)-1);
            $host = $host[$r];
        }
        return $host;
    }

    public function addClientImgHost(&$data,$clientUrl='') {
        $clientUrl = empty($clientUrl)?$this->getClientImgHost():$clientUrl;
        if (empty($clientUrl)||!isset($data['oss-request-url'])||!isset($data['oss-requestheaders']['Host'])) return false;
        $data['client.image'] = str_replace(["http://{$data['oss-requestheaders']['Host']}","https://{$data['oss-requestheaders']['Host']}"],[$clientUrl,$clientUrl],$data['oss-request-url']);
        return $data;
    }

    /**
     * @return OssClient
     */
    public function getClient() {
        return $this->client;
    }

    public function getBucket() {
        return $this->bucket;
    }
}