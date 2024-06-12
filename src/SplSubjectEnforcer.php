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
    protected $splUpdateAdd = null;
    protected $splUpdateDelete = null;
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
        $this->setSplUpdateAddData(null);
        $this->setSplUpdateDeleteData(null);
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
     * 处理值格式化
     * @param $val
     * @return array|false|string[]
     */
    private function dealValFormat($val)
    {
        if (!empty($val)) {
            if (is_array($val)) {
                return $val;
            } else if (strpos($val, ',') !== false) {
                return explode(',', $val);
            } else {
                return [$val];
            }
        } else {
            return [];
        }
    }

    /**
     * 对比新老数据获取新增或者删除差异
     * @param $changeAdd
     * @param $changeDelete
     * @param $key
     * @param $oldVal
     * @param $newVal
     * @return void
     */
    private function contrastDataChanged(&$changeAdd, &$changeDelete, $key, $oldVal, $newVal)
    {

        $newValArr = $this->dealValFormat($newVal);
        $oldValArr = $this->dealValFormat($oldVal);

        // 获取两个数组不同的元素array_diff,是取第一数组存在，第二个数组不存在值
        $diff = array_merge(array_diff($oldValArr, $newValArr), array_diff($newValArr, $oldValArr));

        if (empty($diff)) {
            return;
        }

        foreach ($diff as $item) {
            if (in_array($item, $oldValArr)) {
                // 老数据存在，新数据不存在，被删除
                $changeDelete[$key][] = $item;
            } else {
                // 老数据不存在，新数据存在，新增
                $changeAdd[$key][] = $item;
            }
        }
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
        $changeAdd = [];
        $changeDelete = [];

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
                        $oldJsonValue = $oldJsonFields[$newJsonKey] ?? [];
                        $this->contrastDataChanged($changeAdd, $changeDelete, $newJsonKey, $oldJsonValue, $newJsonValue);
                    }
                }
                continue;
            }
            if (
                !isset($old[$newKey])
                || $old[$newKey] != $newValue
            ) {
                $changed[$newKey] = $newValue;
                $this->contrastDataChanged($changeAdd, $changeDelete, $newKey, $old[$newKey], $newValue);
            }
        }

        $this->setSplUpdateData($changed);
        $this->setSplUpdateAddData($changeAdd);
        $this->setSplUpdateDeleteData($changeDelete);
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
     * 设置更新字段新增的的数据
     * @return array
     */
    public function getSplUpdateAddData(): array
    {
        return $this->splUpdateAdd;
    }

    /**
     * 获取更新字段删除的的数据
     * @return array
     */
    public function getSplUpdateDeleteData(): array
    {
        return $this->splUpdateDelete;
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
     * 设置更新字段新增的的数据
     * @param $splUpdateAdd
     * @return void
     */
    private function setSplUpdateAddData($splUpdateAdd): void
    {
        $this->splUpdateAdd = $splUpdateAdd;
    }

    /**
     * 设置更新字段删除的的数据
     * @param $splUpdateDelete
     * @return void
     */
    private function setSplUpdateDeleteData($splUpdateDelete): void
    {
        $this->splUpdateDelete = $splUpdateDelete;
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
