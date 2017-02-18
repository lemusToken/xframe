<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/29
 * Time: 17:48
 */

namespace Libs\Security\Token;
use \Libs\Security\Token;

class Md5 extends Token {
    /**
     * 生成token
     * @param array $payload
     * @return string
     */
    public function create(array $payload){
        $secreteKey = $this->getFinalKey();
        return md5(md5(json_encode($payload).$secreteKey));
    }

    public function verify($code) {}
}