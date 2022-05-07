<?php

class ClassTwo extends Test\ClassOne
{
    public function barfoo(): string
    {
        return $this->foobar;
    }
}
