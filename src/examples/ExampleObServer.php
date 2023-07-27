<?php

namespace teamones\dbeventObserver\examples;

use teamones\dbeventObserver\interfaces\SplObserver;
use teamones\dbeventObserver\interfaces\SplSubject;

class ExampleObServer implements SplObserver
{

    public function update(SplSubject $subject): void
    {
        // TODO: Implement update() method.
        // 打印修改的数据
        var_dump($subject->getSplUpdateData());
    }
}
