<?php

namespace teamones\dbeventObserver;

use teamones\dbeventObserver\interfaces\SplObserver;

/**
 * 观察者模型 被观察者的接口实现
 */
trait SplSubjectEnforcer
{
    /** @var SplObserver[] */
    protected $observers = [];
    protected $splUpdateData = null;
    protected $splPrimaryId = null;
    protected $splOldData = null;
    protected $splNewData = null;
    protected $subjectData = null;
    protected $subjectOperate = null;
    protected static $splJsonFields = [
        'json'
    ];

    /**
     * 执行完之后清理本次产生的数据
     * @return void
     */
    public function clean()
    {
        $this->setSplUpdateData(null);
        $this->setSplPrimaryId(null);
        $this->setSplOldData(null);
        $this->setSplNewData(null);
        $this->setSubjectData(null);
        $this->setSubjectOperate(null);
    }

    /**
     * @inheritDoc
     */
    public function attach(SplObserver $observer)
    {
        $this->observers[md5(serialize($observer))] = $observer;
    }

    /**
     * 对比出已经修改的属性
     * @param $old
     * @param $new
     * @return array
     */
    public function splDataChanged($old, $new): array
    {
        $this->setSplNewData($new);
        $this->setSplOldData($old);
        $changed = [];
        // 对比json数据字段
        foreach ($new as $newKey => $newValue) {
            if (in_array($newKey, self::$splJsonFields)) {
                $newJsonFields = json_decode($newValue, true);
                $oldJsonFields = json_decode($old[$newKey] ?? '{}', true);
                foreach ($newJsonFields as $newJsonKey => $newJsonValue) {
                    if (
                        !isset($oldJsonFields[$newJsonKey])
                        || $oldJsonFields[$newJsonKey] != $newJsonValue
                    ) {
                        $changed[$newJsonKey] = $newJsonValue;
                    }
                }
                continue;
            }
            if (
                !isset($old[$newKey])
                || $old[$newKey] != $newValue
            ) {
                $changed[$newKey] = $newValue;
            }
        }
        $this->setSplUpdateData($changed);
        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function detach(SplObserver $observer)
    {
        unset($this->observers[md5(serialize($observer))]);
    }

    /**
     * @inheritDoc
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
        $this->clean();
    }

    /**
     * @inheritDoc
     */
    public function getSplUpdateData(): array
    {
        return $this->splUpdateData;
    }

    /**
     * 获得数据主键
     * @inheritDoc
     */
    public function getSplPrimaryId()
    {
        return $this->splPrimaryId;
    }

    /**
     * @inheritDoc
     */
    public function setSplPrimaryId($splPrimaryId): void
    {
        $this->splPrimaryId = $splPrimaryId;
    }

    /**
     * 获得老数据
     * @inheritDoc
     */
    public function getSplOldData(): array
    {
        return $this->splOldData;
    }


    /**
     * 获得新数据
     * @inheritDoc
     */
    public function getSplNewData(): array
    {
        return $this->splNewData;
    }

    /**
     * 设置老数据
     * @param $splOldData
     * @return void
     */
    private function setSplOldData($splOldData): void
    {
        $this->splOldData = $splOldData;
    }

    /**
     * 设置新数据
     * @param $splNewData
     * @return void
     */
    private function setSplNewData($splNewData): void
    {
        $this->splNewData = $splNewData;
    }

    /**
     * 设置更新的数据
     * @param $splUpdateData
     * @return void
     */
    private function setSplUpdateData($splUpdateData): void
    {
        $this->splUpdateData = $splUpdateData;
    }

    /**
     * 设置当前对象的完整data
     */
    public function setSubjectData($subjectData): void
    {
        $this->subjectData = $subjectData;
    }

    /**
     * 获得当前对象的完整data
     * @return array
     */
    public function getSubjectData(): array
    {
        return $this->subjectData;
    }

    /**
     * 设置当前对象的操作类型
     * @param $operate
     * @return void
     */
    public function setSubjectOperate($operate): void
    {
        $this->subjectOperate = $operate;
    }

    /**
     * 获得当前对象的完整data
     * @return string
     */
    public function getSubjectOperate(): string
    {
        return $this->subjectOperate;
    }

}
