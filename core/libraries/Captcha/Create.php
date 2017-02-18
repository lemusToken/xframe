<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/27
 * Time: 21:37
 */

namespace Libs\Captcha;

/**
 * 生成图片验证码
 * Class Create
 * @package Libs\Captcha
 */
class Create {

    public static function png($char,$width=100,$height=40,$fontsize=22) {
        $font = __DIR__.'/SECRCODE.TTF';
        $im=imagecreatetruecolor($width,$height);
        $while=imageColorAllocate($im,255,255,255);
        imagefill($im,0,0,$while); //填充图像
        $codelen = strlen($char);
        //start
        self::drawStar($im,$width,$height);
        //line
        self::drawLine($im,$codelen,$width,$height);
        //text
        self::drawText($im,$width,$height,$char,$font,$fontsize);
        //生成图像
        header("content-type:image/PNG");
        imagePNG($im);
        imageDestroy($im);
    }

    protected static function drawStar($im,$width,$height) {
        for ($i=0;$i<$width;$i++){
            $randcolor=imageColorallocate($im,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($im,mt_rand(1,5), mt_rand(0,$width),mt_rand(0,$height), '*',$randcolor);
        }
        return $im;
    }

    protected static function drawLine($im,$total,$width,$height) {
        for($i=0;$i<$total;$i++) {
            $randcolor=imagecolorallocate($im,mt_rand(100,255),mt_rand(100,255),mt_rand(100,255));
            imageline($im,0,mt_rand(0,$height),$width,mt_rand(0,$height),$randcolor);
        }
        return $im;
    }

    protected static function drawText($im,$width,$height,$char,$font,$fontsize=22) {
        $codelen = strlen($char);
        $x=intval($width/$codelen); //计算字符距离
        $y=intval($height*0.7); //字符显示在图片70%的位置
        for($i=0;$i<$codelen;$i++){
            $randcolor=imagecolorallocate($im,mt_rand(0,150),mt_rand(0,150),mt_rand(0,150));
            imagettftext($im,$fontsize,mt_rand(-30,30),$i*$x+3,$y,$randcolor,$font,$char[$i]);
        }
    }
}