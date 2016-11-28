<?php

class Foobar
{
    private $var1;

    public function __construct()
    {
        $this->var1 = new BarFoo();
    }

    public function hello(Foobar $foobar, BarFoo $barfoo, string $bar): int
    {
        $zed = 'hello';

        $this->foo('foo');
    }

    public function createBarfoo(): BarFoo
    {
        return new BarFoo();
    }
}

class BarFoo
{
    public function isSomething(): bool
    {
    }
}
