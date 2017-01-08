<?php

namespace Phpactor\Tests\Functional\Generation\classes;

abstract class AbstractExample implements Example1Interface
{
    abstract protected function abstractMethodOne();

    abstract protected function abstractMethodTwo();

    public function concreteMethodOne($barfoo = 'hello')
    {
    }

    public function concreteMethodTwo()
    {
    }
}
