<?php

/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/12/1
 * Time: 19:04
 */
namespace Libs\Ruleset;
use \Libs\Utils\Interpreter\Partition\Main;
use \Libs\Utils\Singleton\InstanceTrait;
use \Libs\Validate\Run;

class Base {
    use InstanceTrait;
    private static $path='';

    public static function setPath($path) {
        self::$path = $path;
    }

    /**
     * 验证数组是否符合规则集
     * @param $config
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public static function validateRuleSet($config,array $data,array $options=[]) {
        if (is_string($config)) {
            $file = self::$path.'/'.$config.'.php';

            if (file_exists($file)) {
                $config =  include $file;
            }
        }

        $ignore = isset($options['ignore'])?$options['ignore']:[];

        $result = [
            'status'=>0,
            'rule'=>'',
            'error'=>''
        ];
        if (!empty($options['strict'])) {
            foreach ($config['rule'] as $k=>$v) {
                if (in_array($k,$ignore)) continue;
                if (!isset($data[$k])) {
                    $result['status'] = 0;
                    $result['rule'] = $v['name'];
                    $result['error'] = $v['tip'];
                    $result['errorName'] = $k;
                    return $result;
                }
            }
        }

        foreach ($data as $n=>$v) {
            $field = $n;
            if (isset($config['alias'][$n])) {
                $field = $config['alias'][$n];
            }

            //规则判断
            $params = [];
            $ruleName = '';
            $tip = '';

            if (!in_array($field,$ignore)&&isset($config['rule'][$field])) {
                $params = isset($config['rule'][$field]['params'])?$config['rule'][$field]['params']:[];
                $ruleName = isset($config['rule'][$field]['name'])?$config['rule'][$field]['name']:'';
                $tip = isset($config['rule'][$field]['tip'])?$config['rule'][$field]['tip']:'';
            }

            $r = self::validate($v,$ruleName,[
                'tip'=>$tip,
                'params'=>$params
            ]);
            //一项错误即跳出
            if ($r['status']==0) {
                $result['status'] = 0;
                $result['rule'] = $ruleName;
                $result['error'] = $tip;
                $result['errorName'] = $n;
                return $result;
            }
            $result['data'][$field] = $v;
        }

        $result['status'] = 1;
        return $result;
    }

    /**
     * 验证字符串是否符合规则集
     * @param $str
     * @param $rule
     * @param array $config
     * @return array
     */
    public static function validate($str,$rule,array $config=[]) {
        $result = [
            'status'=>1
        ];
        if (empty($rule)) return $result;

        $params = isset($config['params'])?$config['params']:[];
        $tip = isset($config['tip'])?$config['tip']:'';

        $r = '';
        if (is_callable($rule)) {
            $r = $rule($str);
        }
        else {
            $inst = self::loadInst();
            $r = $inst->runLogical($rule,$str,$params);
        }
        if (!$r) {
            $result['status'] = 0;
            $result['error'] = $tip;
        }

        return $result;
    }

    protected function runLogical($rule,$val,$params=[]) {
        $code = $this->parseLogicalOperator($rule,$val,$params);
        $r = '';
        if ($code) {
            eval('$r='.$code.';');
        }
        else {
            $r = Run::check($rule,$val,$params);
        }
        return $r;
    }

    protected function parseLogicalOperator($rule,$val,$params=[],$codeClass='\\Libs\\Validate\\Run') {
        if (!$this->isExistLogical($rule)) return false;
        $partition = Main::createInst();
        $partition->setTag([
            '!','||','&&','(',')'
        ]);
        $ps = $partition->scanStr($rule);
        $code = '';

        foreach ($ps['text'] as $v) {
            $p = '[]';
            if (isset($params[$v])) {
                $p = var_export($params[$v],true);
            }
            if (in_array($v,$ps['word'])) {
                if (is_int($val)) {
                    $code .= "$codeClass::check('$v',$val,$p)";
                }
                else {
                    $code .= "$codeClass::check('$v','$val',$p)";
                }
            }
            else {
                $code .= $v;
            }
        }
        return $code;
    }

    protected function isExistLogical($str) {
        return strpos($str,'&&')!==false||strpos($str,'||')!==false||strpos($str,'!')!==false;
    }


}