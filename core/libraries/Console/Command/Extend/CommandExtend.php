<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/11/19
 * Time: 11:27
 */

namespace Libs\Console\Command\Extend;
use Libs\Utils\Common;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

abstract class CommandExtend extends Command{

    /**
     * @var \Libs\Console\Helper\Common
     */
    protected $common = null;
    protected $name = '';

    protected function configure() {
        $config = $this->config();

        $this
            ->setName($this->name)
            ->setDescription($this->chs($config['description']));

        $definition = [];
        if (!empty($config['arguments'])) {
            foreach ($config['arguments'] as $v) {
                $definition[] = $this->createArgument(
                    $v[0],
                    isset($v[1])?$v[1]:null,
                    isset($v[2])?$this->chs($v[2]):'',
                    isset($v[3])?$v[3]:null
                );
            }
        }

        if (!empty($config['definition'])) {
            foreach ($config['definition'] as $v) {
                $definition[] = $this->createInput(
                    $v[0],
                    isset($v[1])?$v[1]:null,
                    isset($v[2])?$v[2]:'',
                    isset($v[3])?$this->chs($v[3]):'',
                    isset($v[4])?$v[4]:null
                );
            }
        }
        !empty($definition)&&$this->setDefinition($definition);

        $this->setHelp($this->chs($config['help']));
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->work($input,$output,$this->createIO($input,$output));
    }

    abstract protected function config();

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $line
     * @return mixed
     *
     */
    abstract protected function work($input,$output,$line);

    protected function createArgument($name,$model=null,$description='',$default=null) {
        $ls = [
            'VALUE_REQUIRED'=>InputArgument::REQUIRED,
            'VALUE_OPTIONAL'=>InputArgument::OPTIONAL,
            'VALUE_IS_ARRAY'=>InputArgument::IS_ARRAY,
        ];
        $model = isset($ls[$model])?$ls[$model]:$ls['VALUE_OPTIONAL'];
        return new InputArgument($name,$model,$description,$default);
    }

    protected function createInput($name,$shortcut=null,$model=null,$description='',$default=null) {
        $ls = [
            'VALUE_NONE'=>InputOption::VALUE_NONE,
            'VALUE_REQUIRED'=>InputOption::VALUE_REQUIRED,
            'VALUE_OPTIONAL'=>InputOption::VALUE_OPTIONAL,
            'VALUE_IS_ARRAY'=>InputOption::VALUE_IS_ARRAY,
        ];
        $model = isset($ls[$model])?$ls[$model]:$ls['VALUE_NONE'];
        return new InputOption($name,$shortcut,$model,$description,$default);
    }

    protected function createIO (InputInterface $input,OutputInterface $output){
        return new SymfonyStyle($input,$output);
    }

    protected function createQuestion($str) {
        return new Question($str);
    }

    protected function chs() {
        $os = Common::checkOS();
        //如果是windows系统，php版本小于7.1，转gbk
        if ($os==='win'&&version_compare(PHP_VERSION,'7.1.0')<0) {
            return call_user_func_array('\Libs\Utils\Common::chs',func_get_args());
        }
        return call_user_func_array('\Libs\Utils\Common::combineStr',func_get_args());
    }

    protected function formatPath($path) {
        return str_replace(['\\','/'],[DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR],$path);
    }

    protected function scandirDeep($path,&$data) {
        Common::scandirDeep($path,$data);
    }
}