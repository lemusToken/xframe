<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 16:15
 */

namespace Libs\Console;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Libs\Console\Helper;
use Libs\ORM\Doctrine\EntityManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;


class Console {

    public static function run($name='XAPP Command Line',$version='UNKNOWN') {
        //部分应用会用到
        $_SERVER['SERVER_SOFTWARE'] = isset($_SERVER['SERVER_SOFTWARE'])?$_SERVER['SERVER_SOFTWARE']:
            (isset($_SERVER['OS'])?$_SERVER['OS']:'');
        //创建命令行应用
        $cli = new Application($name,$version);
        //可捕获错误
        $cli->setCatchExceptions(true);

        $helpList = [];
        //自定义或者应用项目中的命令帮助类导入
        if (class_exists('\\App\\Libs\\Console\\Register')) {
            $helpList = array_merge($helpList,call_user_func(['\\App\\Libs\\Console\\Register','helpers']));
        }
        $em = EntityManager::get();
        $helpList = array_merge($helpList,[
            'question'=>new QuestionHelper(),
            'db' => new ConnectionHelper($em->getConnection()),
            'em' => new EntityManagerHelper($em),
            'url'=> new Helper\Url,
            'symbol'=> new Helper\Symbol,
            'appConfig'=> new Helper\AppConfig,
            'pack'=>new Helper\Pack,
            'part'=>new Helper\Partition,
            'common'=>new Helper\Common
        ]);
        //设置命令帮助类
        $cli->setHelperSet(new HelperSet($helpList));

        //自定义或者应用项目中命令导入
        if (class_exists('\\App\\Libs\\Console\\Register')) {
            $listCommandExt = call_user_func(['\\App\\Libs\\Console\\Register','commands']);
            $cli->addCommands($listCommandExt);
        }
        //添加doctrine命令
        ConsoleRunner::addCommands($cli);
        $cli->addCommands([
            new Command\Url,
            new Command\CreateApp,
            new Command\CreateModel,
            new Command\CreateVModel,
            new Command\CreateMap,
            new Command\CreateSession,
            new Command\CreateLibs,
            new Command\StaticPack,
            new Command\CreatePartition,
            new Command\UpdatePartition
        ]);

        $cli->run();
    }

    /**
     * 核心中使用，有限的命令，主要用于生成app应用
     */
    public static function runInCore($name='XCORE Command Line',$version='UNKNOWN') {
        //创建命令行应用
        $cli = new Application($name,$version);
        //可捕获错误
        $cli->setCatchExceptions(true);

        $helpList = [
            'question'=>new QuestionHelper(),
            'common'=>new Helper\Common
        ];
        //设置命令帮助类
        $cli->setHelperSet(new HelperSet($helpList));
        $cli->addCommands([
            new Command\CreateObject
        ]);

        $cli->run();
    }
}