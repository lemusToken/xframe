<?php
/**
 * Created by PhpStorm.
 * User: ��
 * Date: 2016/8/23
 * Time: 9:23
 */

namespace Libs\Utils\Observer;

/**
 * Class Subject
 * 被观察者
 * @package Libs\Utils\Observer
 */
class Subject implements \SplSubject {
    private $observers=[];

    /**
     * 订阅，添加观察者
     * @param \SplObserver $observer
     */
    public function attach(\SplObserver $observer) {
        $this->observers[] = $observer;
    }

    /**
     * 解除，删除观察者
     * @param \SplObserver $observer
     */
    public function detach(\SplObserver $observer) {
        if($index = array_search($observer, $this->observers, true)) {
            unset($this->observers[$index]);
        }
    }

    /**
     * 通知所有的观察者
     */
    public function notify() {
        foreach($this->observers as $observer) {
            $observer->update($this);
        }
    }
}