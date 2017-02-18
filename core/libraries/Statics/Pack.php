<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/10/13
 * Time: 15:43
 */

namespace Libs\Statics;
use \Libs\Utils\Common;


class Pack {

    /**
     * 合并并压缩
     * @param $str
     * @return array
     */
    public static function minify($str,$host) {
        $data = Find::combine($str,$host);
        $cssMin = new \CSSmin;

        //压缩css
        if (!empty($data['css'])) {
            foreach ($data['css'] as $k=>&$v) {
                $v = $cssMin->run(implode('',$v));
            }
        }

        //压缩js
        if (!empty($data['js'])) {
            foreach ($data['js'] as $k=>&$v) {
                $v = \JSMin::minify(implode('',$v));
            }
        }
        return $data;
    }

    /**
     * 生成合并后的静态资源文件
     * @param array $combine 合并压缩后的数据minify返回
     * @param string $base 资源存放目录
     * @param array $params 符号表
     * @return array
     */
    public static function create($combine,$base='',$params=[],$symbol) {
        $result = ['list'=>[],'res'=>[]];
        if(!empty($combine)) {
            foreach ($combine as $type=>$data) {
                if ($type!=='js'&&$type!=='css') continue;
                foreach ($data as $p=>$str) {
                    if (strpos($p,'/')===0) {
                        $path = $p;
                    }
                    elseif ($p==='main') {
                        $path = !empty($params['subappname'])?
                            $base.'/'.$params['appname'].'/'.$params['subappname'].'/'.$type.'/'.$params['app'].'.'.$params['act'].'.main.'.$type:
                            $base.'/'.$params['appname'].'/'.$type.'/'.$params['app'].'.'.$params['act'].'.main.'.$type;
                    }
                    else {
                        $path = $base.'/'.$params['appname'].'/'.$p;
                        if ($symbol&&strpos($path,':')!==false) {
                            $path = $symbol->parse($path,false,$params);
                        }
                    }
                    $basePath = dirname($path);
                    if (!is_dir($basePath)){
                        mkdir($basePath,0777,true);
                    }
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    strpos($path,'.min.'.$ext)===false&&$path = str_replace('.'.$ext,'.min.'.$ext,$path);
                    $result['res'][basename($path)] = $path;
                    //打包后的外部文件 添加静态资源版本号
                    $str = preg_replace_callback('/url\s*?\(\s*?(.*?)\s*?\)/',function($w){
                        $w = $w[0];
                        if (stripos($w,'data:')!==false) {
                            return $w;
                        }
                        if (strpos($w,'?')!==false) {
                            $w = str_replace(')','&v='.time().')',$w);
                        }
                        else {
                            $w = str_replace(')','?v='.time().')',$w);
                        }
                        return $w;
                    },$str);
                    file_put_contents($path,$str);
                }
            }
            $result['list'] = $combine['list'];
        }
        return $result;
    }

    /**
     * 在html代码中添加js资源（尾部）
     * @param $content
     * @param $js
     * @return mixed|string
     */
    public static function addJS($content,$js) {
        $result = '';
        if (($p=stripos($content,$r='</body>'))!==false) {
            $result = substr($content,0,$p).$js.substr($content,$p);
        }
        elseif (($p=stripos($content,$r='</html>'))!==false) {
            $result = substr($content,0,$p).$js.substr($content,$p);
        }
        elseif (($p=strpos($content,$r='{% block assets_footer %}'))!==false) {
            //匹配block
            $ps = Common::matchStartEnd($content,'{% block','{% endblock %}');
            foreach ($ps as $k=>$v) {
                if (substr($content,$v[0],strlen($r))===$r) {
                    $_p = $v[1]-strlen('{% endblock %}');
                    $result = substr($content,0,$_p).$js.substr($content,$_p);
                    break;
                }
            }
        }
        else {
            $result = $content.$js;
        }
        return $result;
    }

    /**
     * 在html代码中添加css资源（头部）
     * @param $content
     * @param $css
     * @return mixed|string
     */
    public static function addCSS($content,$css) {
        $result = '';
        if (stripos($content,'</head>')!==false) {
            $result = str_replace('</head>',$css."\n</head>",$content);
        }
        elseif (stripos($content,'<body>')!==false) {
            $result = str_replace('<body>',"<body>\n".$css,$content);
        }
        elseif (stripos($content,'<html>')!==false) {
            $result = str_replace('<html>',"<html>\n".$css,$content);
        }
        elseif (($p=strpos($content,$r='{% block assets_header %}'))!==false) {
            //匹配block
            $ps = Common::matchStartEnd($content,'{% block','{% endblock %}');
            foreach ($ps as $k=>$v) {
                if (substr($content,$v[0],strlen($r))===$r) {
                    $_p = $v[1]-strlen('{% endblock %}');
                    $result = substr($content,0,$_p).$css.substr($content,$_p);
                    break;
                }
            }
        }
        else {
            $result = $css.$content;
        }
        return $result;
    }
}