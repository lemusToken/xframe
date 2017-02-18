<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/27
 * Time: 17:01
 */

namespace Libs\Security\Token;

use \Libs\Utils\Cryption\Exor as LibExor;
use \Libs\Security\Token;

class Exor extends Token {

    /**
     * 生成token
     * @param array $payload
     * @return string
     */
    public function create(array $payload){
        $secreteKey = $this->getFinalKey();
        return base64_encode(LibExor::code($secreteKey,json_encode($payload)));
    }

    /**
     * 校验token并解析token数据
     * @param $code
     * @return mixed|null
     */
    public function verify($code) {
        $secreteKey = $this->getFinalKey();
        $data = json_decode(LibExor::code($secreteKey,base64_decode($code)),true);
        //过期
        if (!empty($data['exp'])&&time()>$data['exp']) return null;
        return $data;
    }

}