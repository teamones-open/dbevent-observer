<?php

namespace teamones\dbeventObserver\examples;

use teamones\dbeventObserver\interfaces\SplSubject;
use teamones\dbeventObserver\SplSubjectEnforcer;

class ExampleModel implements SplSubject
{
    use SplSubjectEnforcer;
}
