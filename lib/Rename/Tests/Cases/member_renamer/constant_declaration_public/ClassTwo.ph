<?php

class ClassTwo extends ClassOne
{
    public function barfoo(): string
    {
        return parent::FOO;
    }

    public function barzoo(): string
    {
        return parent::ZOO;
    }
}
