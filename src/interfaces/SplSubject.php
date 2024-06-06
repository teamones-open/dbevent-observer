<?php

namespace teamones\dbeventObserver\interfaces;

/**
 * 观察者模型 被观察模型接口
 */
interface SplSubject
{
    /**
     * 加载观察者
     * @param SplObserver $observer
     * @return mixed
     */
    public function attach(SplObserver $observer);

    /**
     * 移出观察者
     * @param SplObserver $observer
     * @return mixed
     */
    public function detach(SplObserver $observer);

    /**
     * 对比历史数据
     * @param $old
     * @param $new
     * @return mixed
     */
    public function splDataChanged($old, $new);

    /**
     * 通知已加载的观察者
     * @return mixed
     */
    public function notify();

    /**
     * 获得当前更新的数据
     * @return array
     */
    public function getSplUpdateData(): array;

    /**
     * 获得当前数据主键
     * @return null
     */
    public function getSplPrimaryId();

    /**
     * 设置当前数据主键
     * @param null $splPrimaryId
     */
    public function setSplPrimaryId($splPrimaryId): void;

    /**
     * 获得修改的老值
     * @return array
     */
    public function getSplOldData(): array;

    /**
     * 获得修改的新值
     * @return array
     */
    public function getSplNewData(): array;

    /**
     * 设置当前对象的完整data
     * @param array $subjectData
     * @return void
     */
    public function setSubjectData(array $subjectData): void;

    /**
     * 获得当前对象的完整data
     * @return array
     */
    public function getSubjectData(): array;

    /**
     * 设置当前对象的操作类型
     * @param $operate
     * @return void
     */
    public function setSubjectOperate($operate): void;

    /**
     * 获得当前对象的完整data
     * @return string
     */
    public function getSubjectOperate(): string;
}
