<?php
/**
 * Created by PhpStorm.
 * User: xule
 * Date: 2016/8/29
 * Time: 18:10
 */

namespace Libs\Console\Helper;
use \Libs\PartitionTable\Create;
use \Libs\Console\Helper\Extend\HelperExtend;

class Partition extends HelperExtend{

    public function getSql($type) {
        return Create::createSql($type);
    }

    public function updateSql($sql) {
        return Create::updateSql($sql);
    }

    public function getName() {
        return 'part';
    }
}