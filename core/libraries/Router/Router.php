<?php

namespace Libs\Router;

/**
 * 路由添加以及匹配
 * Class Router
 * @package Router
 * @author xule
 */
class Router{
    static $queue=[];
    static $queueCaller=[];
    static $queueQuery=[];

    /**
     * 添加路由
     * @param $route
     */
    public static function add($route){
        //参数列表
        $args = func_get_args();

        $code = null;
        $query = null;

        if (is_callable($args[1])){
            $code = $args[1];
            $query = $args[2];
        }
        elseif (is_string($args[1])){
            $query = $args[1];
            $code = $args[2];
        }

        //大路由表时，需要优化查询算法
        self::$queue[] = $route;
        $code&&self::$queueCaller[$route] = $code;
        $query&&self::$queueQuery[$route] = $query;
    }

    /**
     * 路由匹配
     * @param $rule
     * @param string $uri
     * @return array
     */
    public static function match($rule,$uri=''){
        empty($uri)&&($uri = $_SERVER['REQUEST_URI']);
        $params = [];
        $match = [
            'pass'=>false,
            'matchReg'=>[],
            'params'=>[],
            'path'=>''
        ];
        $match['pass'] = false;

        if ($rule==='/'&&($uri===''||$uri==='/')) {
            $match['pass'] = true;
            return $match;
        }

        $uri = trim($uri,'/');
        $rule = trim($rule,'/');

        //规则为空时，匹配结束
        if (empty($rule)){
            $match['pass'] = false;
            return $match;
        }
        //全匹配模式，规则与uri相等时
        if ($uri===$rule){
            $match['pass'] = true;
        }
        //参数匹配模式
        elseif (($p=strpos($rule,':'))!==false){
            $match['pass'] = true;
            $match['matchReg'] = [$uri];

            //提取第一个:之前的符号
            $char = substr($rule,$p-1,1);

            $uri = $char.$uri.$char;
            $rule = $char.$rule.$char;

            $pUriStart = 0;
            $pRuleStart = 0;

            //逐项匹配
            while(1){
                $pUriEnd = strpos($uri,$char,$pUriStart+1);
                $pRuleEnd = strpos($rule,$char,$pRuleStart+1);

                //片段不符合时
                if (($pRuleEnd!==false&&$pUriEnd===false)||($pUriEnd===false&&$pRuleEnd!==false)){
                    $match['pass'] = false;
                    break;
                }
                if ($pRuleEnd===false&&$pUriEnd===false){
                    break;
                }

                $sUri = substr($uri,$pUriStart+1,$pUriEnd-$pUriStart-1);
                $sRule = substr($rule,$pRuleStart+1,$pRuleEnd-$pRuleStart-1);
                if (strpos($sRule,':')!==false){
                    $params[substr($sRule,1)] = $sUri;
                }
                elseif ($sUri!==$sRule){
                    strpos($sRule,'^')===false&&$sRule='^'.$sRule;
                    strpos($sRule,'$')===false&&$sRule=$sRule.'$';
                    $isM = preg_match("|$sRule|",$sUri,$m);
                    if (!$isM){
                        $match['pass'] = false;
                        break;
                    }
                    else if ($m[1]){
                        $match['matchReg'] = array_merge($match['matchReg'],array_unique($m));
                    }
                }

                $pUriStart = $pUriEnd;
                $pRuleStart = $pRuleEnd;
            }

            if (!empty($params)){
                $last = end($params);
                if (($p=strpos($last,'.'))!==false){
                    $last = substr($last,0,$p);
                    $l = array_slice($params,-1,1);
                    array_pop($params);
                    $params[substr(key($l),0,strpos(key($l),'.'))] = $last;
                }
            }
        }
        //正则模式
        else {
            strpos($rule,'^')!==0&&$rule='^'.$rule;
            strpos($rule,'$')!==0&&$rule=$rule.'$';
            $match['pass'] = preg_match("|$rule|",$uri,$m);
            $match['matchReg'] = $m;
        }

        //如果存在小数点
        if ($match['pass']&&strrpos($uri,'.')!==false&&($p=strrpos($uri,'.php/'))!==false){
            $match['path'] = substr($uri,$p+5);
        }

        $match['params'] = $params;

        return $match;
    }

    /**
     * 查询所有路由
     * @param $uri
     * @return Data
     */
    public static function run($uri=''){
        $Data = Data::singleton();
        $data = $Data::$data;
        foreach (self::$queue as $v){
            $data[$v] = empty($data[$v])?[]:$data[$v];
            $r = self::_run($v,$data[$v],$uri);
            //匹配失败
            if (!$r) continue;

            //如果有参数
            if ($r['info']['params']){
                foreach ($r['info']['params'] as $kk=>$vv){
                    $Data->set($kk,$vv);
                }
            }
            //如果有定义数据
            if ($r['data']){
                foreach ($r['data'] as $kk=>$vv){
                    $Data->set($kk,$vv);
                }
            }
            //如何有查询语句
            if ($c=self::$queueQuery[$v]){
                $p = Url::params($c);
                foreach ($p as $kk=>$vv){
                    $vv = self::replaceColon($vv,$r['info']['params']);
                    $vv = self::replaceRegDollar($vv,$r['info']['matchReg']);
                    $Data->set($kk,$vv);
                }
            }
            $next = false;
            //运行路由定义的方法
            if (isset(self::$queueCaller[$v])&&is_callable($c=self::$queueCaller[$v])){
                $next = $c($Data);
            }
            else if(is_bool($c)){
                $next = $c;
            }
            if (!$next){
                break;
            }
        }
        return $Data;
    }

    private static function _run($rule,$data=[],$uri=''){
        $match = self::match($rule,$uri);
        if (!$match['pass']){
            return false;
        }

        if ($data&&$match['matchReg']){
            foreach ($data as $k=>$v){
                $data[$k] = self::replaceRegDollar($v,$match['matchReg']);
            }
        }

        return [
            'info'=>$match,
            'data'=>$data
        ];
    }

    private static function replaceRegDollar($str,$d){
        //存在$
        if (strpos($str,'$')!==false&&!empty($d)){

            //正则匹配所有的$以及后面紧邻的所有数字
            preg_match_all('|\$(\d+)|',$str,$m);

            if (!empty($m[1])){
                foreach ($m[1] as $k=>$v){
                    if (strpos($v,'0')===0){
                        $v = 0;
                        $m[0][$k] = '$0';
                    }
                    $str = str_replace($m[0][$k],$d[$v],$str);
                }
            }
        }
        return $str;
    }

    private static function replaceColon($str,$d){
        //存在:
        if (strpos($str,':')===0&&!empty($d)){
            $str = isset($d[substr($str,1)])?$d[substr($str,1)]:$str;
        }
        return $str;
    }
}